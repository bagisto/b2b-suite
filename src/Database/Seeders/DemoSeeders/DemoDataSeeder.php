<?php

namespace Webkul\B2BSuite\Database\Seeders\DemoSeeders;

use Illuminate\Support\Facades\DB;

/**
 * Orchestrates the demo-data seeders. It first wipes any existing demo data (so re-runs are
 * idempotent) and then seeds companies, their credit lines, quotations and purchase orders in
 * dependency order. Volumes are tunable via the public properties — set them from the
 * `b2b-suite:seed-demo` command (or override before calling `db:seed`) to scale from a handful
 * of companies up to tens of thousands.
 */
class DemoDataSeeder extends DemoSeeder
{
    /**
     * Number of demo companies to create.
     */
    public int $companies = 25;

    /**
     * Maximum member sub-users per company (a random count up to this is created).
     */
    public int $members = 3;

    /**
     * Maximum quotations per company.
     */
    public int $quotations = 4;

    /**
     * Maximum purchase orders per company.
     */
    public int $purchaseOrders = 3;

    /**
     * The demo seeders to run, in dependency order.
     */
    protected array $seeders = [
        DemoCompanySeeder::class,
        DemoCompanyCreditSeeder::class,
        DemoCompanyCatalogSeeder::class,
        DemoQuotationSeeder::class,
        DemoPurchaseOrderSeeder::class,
    ];

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(array $parameters = [])
    {
        $this->clean();

        $parameters = array_merge([
            'companies' => $this->companies,
            'members' => $this->members,
            'quotations' => $this->quotations,
            'purchase_orders' => $this->purchaseOrders,
            'locale' => app()->getLocale(),
            'channel' => core()->getCurrentChannel()->code,
            'currency' => core()->getBaseCurrencyCode(),
            'now' => now()->toDateTimeString(),
        ], $parameters);

        $this->withoutQueryEvents(function () use ($parameters) {
            foreach ($this->seeders as $seeder) {
                app($seeder)->run($parameters);
            }
        });
    }

    /**
     * Remove every demo record. Demo purchase orders create real sales orders, so those are
     * removed first (their items / addresses / payment cascade); deleting the demo customers
     * then cascades to their company flat, attribute values, credit (+ transactions), quotes
     * (+ items / messages), roles and pivots.
     *
     * @return void
     */
    public function clean()
    {
        $this->withoutQueryEvents(function () {
            /**
             * Resolve the demo catalogs (and their hidden groups) up front — the customers must
             * be deleted before the groups can be dropped.
             */
            $catalogGroupIds = DB::table('b2b_company_catalogs')
                ->where('name', 'like', '%'.DemoCompanyCatalogSeeder::DEMO_TAG)
                ->whereNotNull('customer_group_id')
                ->pluck('customer_group_id');

            $customerIds = DB::table('customers')
                ->where('email', 'like', '%@'.self::DEMO_DOMAIN)
                ->pluck('id');

            if ($customerIds->isNotEmpty()) {
                DB::table('orders')->whereIn('customer_id', $customerIds)->delete();
            }

            DB::table('customers')
                ->where('email', 'like', '%@'.self::DEMO_DOMAIN)
                ->delete();

            /**
             * Drop the demo catalogs (their product / category links cascade) along with their
             * group price index and the hidden groups themselves.
             */
            DB::table('b2b_company_catalogs')
                ->where('name', 'like', '%'.DemoCompanyCatalogSeeder::DEMO_TAG)
                ->delete();

            if ($catalogGroupIds->isNotEmpty()) {
                DB::table('product_customer_group_prices')->whereIn('customer_group_id', $catalogGroupIds)->delete();
                DB::table('product_price_indices')->whereIn('customer_group_id', $catalogGroupIds)->delete();
                DB::table('customer_groups')->whereIn('id', $catalogGroupIds)->delete();
            }
        });
    }
}
