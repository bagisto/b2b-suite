<?php

namespace Webkul\B2BSuite\Database\Seeders\DemoSeeders;

use Illuminate\Support\Facades\DB;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

/**
 * Seeds demo purchase orders. A purchase order is a `b2b_customer_quotes` row with
 * `state = purchase_order` that is backed by a **real placed sales order** — mirroring the
 * production flow where placing a "Pay By Credit" order converts the quote into a PO. For each
 * PO this seeder creates the order (+ items, billing/shipping address, payment), links the quote
 * to it, and charges the company's credit line (a "purchased" ledger entry that draws down the
 * outstanding balance), so credit, orders and POs all stay consistent.
 */
class DemoPurchaseOrderSeeder extends AbstractQuoteSeeder
{
    /**
     * Running sequence for unique order increment ids within a run.
     */
    protected int $orderSequence = 0;

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(array $parameters = [])
    {
        $now = $parameters['now'] ?? now()->toDateTimeString();
        $perCompany = max(0, (int) ($parameters['purchase_orders'] ?? 3));
        $agentId = DB::table('admins')->orderBy('id')->value('id');
        $products = $this->products();

        if ($products->isEmpty() || $perCompany < 1) {
            return;
        }

        $this->orderSequence = (int) (DB::table('orders')->max('increment_id') ?? 0);

        $this->demoCompanyIds()->chunk(self::COMPANY_BATCH)->each(function ($companyIds) use ($perCompany, $agentId, $now) {
            $creators = $this->creatorsByCompany($companyIds);
            $flats = DB::table('b2b_company_flat')->whereIn('customer_id', $companyIds)->get()->keyBy('customer_id');
            $credits = DB::table('b2b_company_credits')->whereIn('company_id', $companyIds)->get()->keyBy('company_id');

            $plans = $this->planPurchaseOrders($companyIds, $creators, $perCompany);

            if (empty($plans)) {
                return;
            }

            $orderIdByNumber = $this->createOrders($plans, $flats, $now);
            $quoteIdByNumber = $this->createQuotes($plans, $orderIdByNumber, $agentId, $now);

            $this->createQuoteChildren($plans, $quoteIdByNumber, $agentId, $now);
            $this->chargeCredits($plans, $orderIdByNumber, $credits, $now);
        });
    }

    /**
     * Plan every purchase order for the chunk, keyed by its unique quotation number.
     */
    protected function planPurchaseOrders($companyIds, $creators, int $perCompany): array
    {
        $plans = [];
        $catalogPools = $this->catalogPools();

        foreach ($companyIds as $companyId) {
            $candidates = $creators->get($companyId) ?? collect();
            $pool = $catalogPools[$companyId] ?? $this->products();

            foreach (range(1, random_int(max(1, (int) ($perCompany / 2)), $perCompany)) as $ignored) {
                $creator = $candidates->isNotEmpty() ? $candidates->random() : null;

                $plan = $this->planQuote($pool, ['ordered']);
                $plan['company_id'] = $companyId;
                $plan['creator'] = $creator;
                $plan['increment_id'] = ++$this->orderSequence;

                $number = 'PO-'.now()->format('Y').'-'.str_pad((string) $this->orderSequence, 6, '0', STR_PAD_LEFT);
                $plans[$number] = $plan;
            }
        }

        return $plans;
    }

    /**
     * Create the sales orders (one per PO) and return order id keyed by quotation number.
     */
    protected function createOrders(array $plans, $flats, string $now): array
    {
        $orderRows = [];

        foreach ($plans as $number => $plan) {
            $creator = $plan['creator'];
            $qty = array_sum(array_column($plan['items'], 'negotiated_qty'));
            $total = $plan['negotiated_total'];

            $orderRows[] = [
                'increment_id' => $plan['increment_id'],
                /**
                 * Demo orders are left "pending" on purpose: completing an order requires a
                 * real invoice and shipment to be generated, which the seeder does not create.
                 * A pending order has nothing invoiced yet.
                 */
                'status' => 'pending',
                'channel_name' => 'Default',
                'is_guest' => 0,
                'is_gift' => 0,
                'customer_email' => $creator?->email,
                'customer_first_name' => $creator?->first_name,
                'customer_last_name' => $creator?->last_name,
                'shipping_method' => 'free_free',
                'shipping_title' => 'Free Shipping - Free Shipping',
                'shipping_description' => 'Free Shipping',
                'total_item_count' => count($plan['items']),
                'total_qty_ordered' => $qty,
                'base_currency_code' => 'USD',
                'channel_currency_code' => 'USD',
                'order_currency_code' => 'USD',
                'grand_total' => $total,
                'base_grand_total' => $total,
                'sub_total' => $total,
                'base_sub_total' => $total,
                'customer_id' => $creator?->id,
                'customer_type' => Customer::class,
                'channel_id' => core()->getCurrentChannel()->id,
                'channel_type' => Channel::class,
                'cart_id' => 0,
                'created_at' => $plan['order_date'],
                'updated_at' => $now,
            ];
        }

        $this->bulkInsert('orders', $orderRows);

        $orderIdByIncrement = DB::table('orders')
            ->whereIn('increment_id', array_column($plans, 'increment_id'))
            ->pluck('id', 'increment_id');

        $orderIdByNumber = [];
        $itemRows = [];
        $addressRows = [];
        $paymentRows = [];

        foreach ($plans as $number => $plan) {
            $orderId = $orderIdByIncrement[$plan['increment_id']] ?? null;

            if (! $orderId) {
                continue;
            }

            $orderIdByNumber[$number] = $orderId;

            foreach ($plan['items'] as $item) {
                $itemRows[] = [
                    'sku' => $item['sku'],
                    'type' => $item['type'],
                    'name' => $item['name'],
                    'weight' => 0,
                    'total_weight' => 0,
                    'qty_ordered' => $item['negotiated_qty'],
                    'price' => $item['negotiated_price'],
                    'base_price' => $item['negotiated_price'],
                    'total' => $item['negotiated_total'],
                    'base_total' => $item['negotiated_total'],
                    'product_id' => $item['product_id'],
                    'product_type' => Product::class,
                    'order_id' => $orderId,
                    'created_at' => $plan['order_date'],
                    'updated_at' => $now,
                ];
            }

            foreach ($this->addressRows($orderId, $plan, $flats, $now) as $address) {
                $addressRows[] = $address;
            }

            $paymentRows[] = [
                'order_id' => $orderId,
                'method' => 'paybycredit',
                'method_title' => 'Pay By Credit',
                'created_at' => $plan['order_date'],
                'updated_at' => $now,
            ];
        }

        $this->bulkInsert('order_items', $itemRows);
        $this->bulkInsert('addresses', $addressRows);
        $this->bulkInsert('order_payment', $paymentRows);

        return $orderIdByNumber;
    }

    /**
     * Build the billing + shipping address rows for an order from the company profile.
     */
    protected function addressRows(int $orderId, array $plan, $flats, string $now): array
    {
        $flat = $flats->get($plan['company_id']);
        $creator = $plan['creator'];

        $base = [
            'order_id' => $orderId,
            'customer_id' => $creator?->id,
            'first_name' => $creator?->first_name ?? ($flat->first_name ?? 'Company'),
            'last_name' => $creator?->last_name ?? ($flat->last_name ?? 'Buyer'),
            'company_name' => $flat->business_name ?? null,
            'address' => $flat->address ?? '1 Commerce Way',
            'city' => $flat->city ?? 'New York',
            'state' => $flat->state ?? 'NY',
            'country' => $flat->country ?? 'US',
            'postcode' => $flat->postcode ?? '10001',
            'email' => $creator?->email ?? ($flat->email ?? null),
            'phone' => $flat->phone ?? null,
            'created_at' => $plan['order_date'],
            'updated_at' => $now,
        ];

        return [
            array_merge($base, ['address_type' => 'order_billing']),
            array_merge($base, ['address_type' => 'order_shipping']),
        ];
    }

    /**
     * Create the purchase-order quotes linked to their orders; return quote id by number.
     */
    protected function createQuotes(array $plans, array $orderIdByNumber, ?int $agentId, string $now): array
    {
        $quoteRows = [];

        foreach ($plans as $number => $plan) {
            $creator = $plan['creator'];

            $quoteRows[] = [
                'quotation_number' => $number,
                'po_number' => str_replace('PO-', 'PO/', $number),
                'name' => $plan['name'],
                'description' => $plan['description'],
                'company_id' => $plan['company_id'],
                'customer_id' => $creator?->id,
                'customer_name' => $creator ? trim($creator->first_name.' '.$creator->last_name) : null,
                'customer_email' => $creator?->email,
                'agent_id' => $agentId,
                'total' => $plan['total'],
                'base_total' => $plan['total'],
                'negotiated_total' => $plan['negotiated_total'],
                'base_negotiated_total' => $plan['negotiated_total'],
                'discount_type' => $plan['discount'] > 0 ? 'percent' : null,
                'discount_value' => $plan['discount'] > 0 ? $plan['discount'] : null,
                'order_date' => $plan['order_date'],
                'expected_arrival_date' => $plan['expected_arrival_date'],
                'expiration_date' => $plan['expiration_date'],
                'state' => 'purchase_order',
                'status' => $plan['status'],
                'order_id' => $orderIdByNumber[$number] ?? null,
                'created_at' => $plan['order_date'],
                'updated_at' => $now,
            ];
        }

        $this->bulkInsert('b2b_customer_quotes', $quoteRows);

        return DB::table('b2b_customer_quotes')
            ->whereIn('quotation_number', array_keys($plans))
            ->pluck('id', 'quotation_number')
            ->all();
    }

    /**
     * Insert the quote line items and message thread for each purchase order.
     */
    protected function createQuoteChildren(array $plans, array $quoteIdByNumber, ?int $agentId, string $now): void
    {
        $itemRows = [];
        $messageRows = [];

        foreach ($plans as $number => $plan) {
            $quoteId = $quoteIdByNumber[$number] ?? null;

            if (! $quoteId) {
                continue;
            }

            foreach ($plan['items'] as $item) {
                $itemRows[] = array_merge($item, [
                    'customer_quote_id' => $quoteId,
                    'status' => $plan['status'],
                    'created_at' => $plan['order_date'],
                    'updated_at' => $now,
                ]);
            }

            foreach ($plan['messages'] as $message) {
                $messageRows[] = [
                    'quote_id' => $quoteId,
                    'user_id' => $message['from'] === 'admin' ? ($agentId ?? 1) : ($plan['creator']?->id ?? $agentId ?? 1),
                    'user_type' => $message['from'],
                    'message' => $message['body'],
                    'created_at' => $message['at'],
                    'updated_at' => $message['at'],
                ];
            }
        }

        $this->bulkInsert('b2b_customer_quote_items', $itemRows);
        $this->bulkInsert('b2b_customer_quote_messages', $messageRows);

        $this->attachQuotations($plans, $quoteIdByNumber);
    }

    /**
     * Draw down each company's credit line for its purchase orders, recording a "purchased"
     * ledger entry per order and updating the outstanding balance.
     */
    protected function chargeCredits(array $plans, array $orderIdByNumber, $credits, string $now): void
    {
        $byCompany = [];

        foreach ($plans as $number => $plan) {
            if (isset($orderIdByNumber[$number])) {
                $byCompany[$plan['company_id']][] = [
                    'order_id' => $orderIdByNumber[$number],
                    'amount' => (float) $plan['negotiated_total'],
                    'at' => $plan['order_date'],
                ];
            }
        }

        $transactionRows = [];

        foreach ($byCompany as $companyId => $orders) {
            $credit = $credits->get($companyId);

            if (! $credit) {
                continue;
            }

            $limit = (float) $credit->credit_limit;
            $ceiling = $credit->allow_exceed_limit ? $limit * 1.25 : $limit;
            $outstanding = (float) $credit->outstanding_balance;

            usort($orders, fn ($a, $b) => strcmp($a['at'], $b['at']));

            foreach ($orders as $order) {
                $charge = round(min($order['amount'], max(0, $ceiling - $outstanding)), 2);

                if ($charge <= 0) {
                    continue;
                }

                $outstanding = round($outstanding + $charge, 2);

                $transactionRows[] = [
                    'company_credit_id' => $credit->id,
                    'operation' => 'purchased',
                    'amount' => $charge,
                    'outstanding_balance_after' => $outstanding,
                    'available_credit_after' => round($limit - $outstanding, 2),
                    'credit_limit_after' => $limit,
                    'order_id' => $order['order_id'],
                    'comment' => 'Purchase order placed against the credit line.',
                    'actor_type' => 'customer',
                    'actor_id' => $companyId,
                    'created_at' => $order['at'],
                    'updated_at' => $now,
                ];
            }

            DB::table('b2b_company_credits')->where('id', $credit->id)->update(['outstanding_balance' => $outstanding]);
        }

        $this->bulkInsert('b2b_company_credit_transactions', $transactionRows);
    }
}
