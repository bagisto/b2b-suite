<?php

namespace Webkul\B2BSuite\Database\Seeders\DemoSeeders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Shared builder for the `b2b_customer_quotes` table, which backs both quotations
 * (`state = quotation`) and purchase orders (`state = purchase_order`). Concrete seeders pick
 * the state, the candidate statuses and how many to create per company; this base handles the
 * realistic line items, totals, negotiation amounts and the message thread.
 */
abstract class AbstractQuoteSeeder extends DemoSeeder
{
    /**
     * Running counter used to mint unique quotation / PO numbers within a single run.
     */
    protected int $sequence = 0;

    /**
     * Cached pool of priceable storefront products to draw line items from.
     */
    protected ?Collection $products = null;

    /**
     * Cached per-company catalog product pools (company id → priced products). A company on a
     * catalog quotes its own allowlisted products at its negotiated catalog rate instead of the
     * base storefront price.
     */
    protected ?array $catalogPools = null;

    /**
     * Build quotes/POs for every demo company.
     */
    protected function seedQuotes(array $parameters, string $state, array $statuses, string $numberPrefix, int $perCompany): void
    {
        $now = $parameters['now'] ?? now()->toDateTimeString();
        $agentId = DB::table('admins')->orderBy('id')->value('id');
        $products = $this->products();

        if ($products->isEmpty() || $perCompany < 1) {
            return;
        }

        $catalogPools = $this->catalogPools();

        $this->demoCompanyIds()->chunk(self::COMPANY_BATCH)->each(function ($companyIds) use ($state, $statuses, $numberPrefix, $perCompany, $agentId, $products, $catalogPools, $now) {
            $creators = $this->creatorsByCompany($companyIds);

            $quoteRows = [];
            $plans = [];

            foreach ($companyIds as $companyId) {
                $candidates = $creators->get($companyId) ?? collect();
                $pool = $catalogPools[$companyId] ?? $products;

                foreach (range(1, random_int(max(1, (int) ($perCompany / 2)), $perCompany)) as $ignored) {
                    $creator = $candidates->isNotEmpty() ? $candidates->random() : null;
                    $plan = $this->planQuote($pool, $statuses);
                    $plan['creator_id'] = $creator?->id;
                    $number = $numberPrefix.'-'.now()->format('Y').'-'.str_pad((string) (++$this->sequence), 6, '0', STR_PAD_LEFT);

                    $plans[$number] = $plan;

                    $quoteRows[] = [
                        'quotation_number' => $number,
                        'po_number' => $state === 'purchase_order' ? 'PO-'.str_pad((string) $this->sequence, 6, '0', STR_PAD_LEFT) : null,
                        'name' => $plan['name'],
                        'description' => $plan['description'],
                        'company_id' => $companyId,
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
                        'state' => $state,
                        'status' => $plan['status'],
                        'created_at' => $plan['order_date'],
                        'updated_at' => $now,
                    ];
                }
            }

            $this->bulkInsert('b2b_customer_quotes', $quoteRows);

            $quoteIdByNumber = DB::table('b2b_customer_quotes')
                ->whereIn('quotation_number', array_keys($plans))
                ->pluck('id', 'quotation_number');

            $this->seedItemsAndMessages($plans, $quoteIdByNumber, $agentId, $now);
        });
    }

    /**
     * Insert the line items and a short message thread for each planned quote.
     */
    protected function seedItemsAndMessages(array $plans, $quoteIdByNumber, ?int $agentId, string $now): void
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
                    'user_id' => $message['from'] === 'admin' ? ($agentId ?? 1) : ($plan['creator_id'] ?? $agentId ?? 1),
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
     * Attach the negotiated price table (the "quotation") to each quote's seller offer message,
     * so the message renders the same line-item table the real negotiation flow produces. Only
     * quotes that reached an offer get one; an offer that the buyer has agreed to is flagged
     * accepted. Reads the just-inserted items and admin message back to link them.
     */
    protected function attachQuotations(array $plans, $quoteIdByNumber): void
    {
        $offerStatuses = ['negotiation', 'accepted', 'ordered', 'completed'];
        $acceptedStatuses = ['accepted', 'ordered', 'completed'];

        $statusByQuote = [];

        foreach ($plans as $number => $plan) {
            $quoteId = $quoteIdByNumber[$number] ?? null;

            if ($quoteId && in_array($plan['status'], $offerStatuses)) {
                $statusByQuote[$quoteId] = $plan['status'];
            }
        }

        if (empty($statusByQuote)) {
            return;
        }

        $quoteIds = array_keys($statusByQuote);

        $itemsByQuote = DB::table('b2b_customer_quote_items')
            ->whereIn('customer_quote_id', $quoteIds)
            ->get()
            ->groupBy('customer_quote_id');

        $adminMessageByQuote = DB::table('b2b_customer_quote_messages')
            ->whereIn('quote_id', $quoteIds)
            ->where('user_type', 'admin')
            ->get()
            ->keyBy('quote_id');

        $customerNameByQuote = DB::table('b2b_customer_quotes')
            ->whereIn('id', $quoteIds)
            ->pluck('customer_name', 'id');

        $rows = [];

        foreach ($statusByQuote as $quoteId => $status) {
            $message = $adminMessageByQuote->get($quoteId);
            $items = $itemsByQuote->get($quoteId);

            if (! $message || ! $items) {
                continue;
            }

            $accepted = in_array($status, $acceptedStatuses);

            foreach ($items as $item) {
                $rows[] = [
                    'message_id' => $message->id,
                    'quote_id' => $quoteId,
                    'quote_item_id' => $item->id,
                    'sku' => $item->sku,
                    'name' => $item->name,
                    'qty' => $item->negotiated_qty,
                    'discount_type' => null,
                    'discount_value' => null,
                    'price' => $item->negotiated_price,
                    'base_price' => $item->base_negotiated_price,
                    'total' => $item->negotiated_total,
                    'base_total' => $item->base_negotiated_total,
                    'is_accepted' => $accepted,
                    'accepted_by' => $accepted ? ($customerNameByQuote[$quoteId] ?? 'Customer') : '',
                    'created_at' => $message->created_at,
                    'updated_at' => $message->updated_at,
                ];
            }
        }

        $this->bulkInsert('b2b_customer_quote_quotations', $rows);
    }

    /**
     * Plan a single quote — its status, dates, line items, totals and message thread.
     */
    protected function planQuote(Collection $products, array $statuses): array
    {
        $faker = $this->faker();
        $status = $faker->randomElement($statuses);
        $orderDate = $faker->dateTimeBetween('-10 months', '-2 days');

        $negotiated = in_array($status, ['negotiation', 'accepted', 'ordered', 'completed']);
        $discount = $negotiated ? $faker->randomElement([5, 7.5, 10, 12.5, 15]) : 0;

        $items = [];
        $total = 0.0;
        $negotiatedTotal = 0.0;

        foreach ($products->random(random_int(2, 5)) as $product) {
            $qty = random_int(1, 25);
            $price = (float) $product->price;
            $lineTotal = round($price * $qty, 2);
            $negPrice = round($price * (1 - $discount / 100), 2);
            $negLineTotal = round($negPrice * $qty, 2);

            $total += $lineTotal;
            $negotiatedTotal += $negLineTotal;

            $items[] = [
                'product_id' => $product->product_id,
                'type' => $product->type,
                'sku' => $product->sku,
                'name' => $product->name,
                'qty' => $qty,
                'price' => $price,
                'base_price' => $price,
                'total' => $lineTotal,
                'base_total' => $lineTotal,
                'negotiated_qty' => $qty,
                'negotiated_price' => $negPrice,
                'base_negotiated_price' => $negPrice,
                'negotiated_total' => $negLineTotal,
                'base_negotiated_total' => $negLineTotal,
            ];
        }

        return [
            'name' => ucfirst($faker->word()).' '.$faker->randomElement(['Supplies', 'Restock', 'Procurement', 'Order', 'Fulfilment']).' — '.$orderDate->format('M Y'),
            'description' => $faker->sentence(10),
            'status' => $status,
            'discount' => $discount,
            'total' => round($total, 2),
            'negotiated_total' => round($negotiatedTotal, 2),
            'order_date' => $orderDate->format('Y-m-d H:i:s'),
            'expected_arrival_date' => (clone $orderDate)->modify('+'.random_int(7, 30).' days')->format('Y-m-d'),
            'expiration_date' => (clone $orderDate)->modify('+'.random_int(20, 45).' days')->format('Y-m-d'),
            'items' => $items,
            'messages' => $this->planMessages($status, $orderDate),
        ];
    }

    /**
     * Plan a short, status-appropriate buyer/agent message thread. A draft has no thread yet;
     * every other status opens with the buyer's request and then follows the lifecycle, so the
     * conversation always matches the quote's outcome (a rejected quote reads as a decline, a
     * negotiated one as an offer, and so on).
     */
    protected function planMessages(string $status, \DateTime $orderDate): array
    {
        if ($status === 'draft') {
            return [];
        }

        $faker = $this->faker();
        $day = 0;

        $line = fn (string $from, string $body) => [
            'from' => $from,
            'body' => $body,
            'at' => (clone $orderDate)->modify('+'.(++$day).' days')->format('Y-m-d H:i:s'),
        ];

        $thread = [
            $line('customer', $faker->randomElement([
                'Could you confirm availability and your best price for these quantities?',
                'Please share a quotation for the items listed.',
                'We are looking to place a recurring order for these items.',
            ])),
        ];

        /**
         * "open" is still awaiting the seller's first response — leave it at the buyer's
         * request. Everything else gets the matching seller reply (and buyer close where due).
         */
        switch ($status) {
            case 'negotiation':
                $thread[] = $line('admin', 'Thanks for the request — we have applied a volume discount, please review the updated figures.');
                $thread[] = $line('customer', 'Appreciated. Could you do a little better on the unit price?');
                break;

            case 'accepted':
                $thread[] = $line('admin', 'We can meet these quantities at the revised pricing below.');
                $thread[] = $line('customer', 'That works for us — we accept the quotation. Please proceed.');
                break;

            case 'ordered':
            case 'completed':
                $thread[] = $line('admin', 'Pricing confirmed at the negotiated totals below.');
                $thread[] = $line('customer', 'Approved — converting this into a purchase order now.');
                break;

            case 'rejected':
                $thread[] = $line('admin', 'Thank you for the request. Unfortunately we are unable to meet these terms at this time.');
                break;

            case 'expired':
                $thread[] = $line('admin', 'Here is our quotation — note that it is valid until the expiration date shown.');
                break;
        }

        return $thread;
    }

    /**
     * Map company id → its linked customers (owner + members) to pick a quote creator from.
     */
    protected function creatorsByCompany($companyIds): Collection
    {
        return DB::table('b2b_customer_companies as cc')
            ->join('customers as c', 'c.id', '=', 'cc.customer_id')
            ->whereIn('cc.company_id', $companyIds)
            ->get(['cc.company_id', 'c.id', 'c.first_name', 'c.last_name', 'c.email'])
            ->groupBy('company_id');
    }

    /**
     * Load (and cache) the pool of priceable storefront products for line items, carrying each
     * product's real type. Non-physical types (booking / virtual / downloadable) are excluded so
     * quotes only contain orderable goods; composite types (configurable / bundle / grouped) have
     * no own price and so are represented by their priceable children (e.g. configurable
     * variants), which is what a quote line ultimately points at anyway.
     */
    protected function products(): Collection
    {
        return $this->products ??= DB::table('product_flat')
            ->join('products', 'products.id', '=', 'product_flat.product_id')
            ->where('product_flat.locale', app()->getLocale())
            ->where('product_flat.status', 1)
            ->whereNotNull('product_flat.sku')
            ->whereNotNull('product_flat.price')
            ->where('product_flat.price', '>', 0)
            ->whereNotIn('products.type', ['booking', 'virtual', 'downloadable'])
            ->inRandomOrder()
            ->limit(200)
            ->get(['product_flat.product_id', 'product_flat.sku', 'product_flat.name', 'product_flat.price', 'products.type']);
    }

    /**
     * Build (and cache) the per-company catalog product pools: company id → the catalog's
     * priceable products with the company's negotiated catalog rate (the group price index's
     * `min_price`). Companies without a catalog are absent, so callers fall back to the base
     * storefront pool. Empty pools are dropped so the fallback still applies.
     */
    protected function catalogPools(): array
    {
        if ($this->catalogPools !== null) {
            return $this->catalogPools;
        }

        $companies = DB::table('customers')
            ->where('type', 'company')
            ->where('email', 'like', '%@'.self::DEMO_DOMAIN)
            ->whereNotNull('company_catalog_id')
            ->whereNotNull('customer_group_id')
            ->pluck('customer_group_id', 'id');

        if ($companies->isEmpty()) {
            return $this->catalogPools = [];
        }

        $productsByGroup = DB::table('product_price_indices')
            ->join('product_flat', function ($join) {
                $join->on('product_flat.product_id', '=', 'product_price_indices.product_id')
                    ->where('product_flat.locale', app()->getLocale());
            })
            ->join('products', 'products.id', '=', 'product_price_indices.product_id')
            ->whereIn('product_price_indices.customer_group_id', $companies->values()->unique())
            ->where('product_flat.status', 1)
            ->whereNotNull('product_flat.sku')
            ->where('product_price_indices.min_price', '>', 0)
            ->whereNotIn('products.type', ['booking', 'virtual', 'downloadable'])
            ->get([
                'product_price_indices.customer_group_id as group_id',
                'product_price_indices.product_id',
                'product_flat.sku',
                'product_flat.name',
                'products.type',
                'product_price_indices.min_price as price',
            ])
            ->groupBy('group_id');

        $pools = [];

        foreach ($companies as $companyId => $groupId) {
            $pool = $productsByGroup->get($groupId);

            if ($pool && $pool->isNotEmpty()) {
                $pools[$companyId] = $pool->values();
            }
        }

        return $this->catalogPools = $pools;
    }
}
