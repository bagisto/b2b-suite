<?php

namespace Webkul\B2BSuite\Database\Seeders\DemoSeeders;

use Illuminate\Support\Facades\DB;
use Webkul\B2BSuite\Helpers\CompanyCatalog;
use Webkul\B2BSuite\Repositories\CompanyCatalogRepository;

/**
 * Seeds a handful of company catalogs and assigns a portion of the demo companies to them.
 *
 * Each catalog is built through the {@see CompanyCatalog} helper so it behaves exactly like one
 * created in the admin: a hidden customer group is provisioned, a product allowlist is synced,
 * the visible categories are derived from those products, and a trade discount (with quantity
 * tiers) is written to the group's price index. A subset of companies (and their members) are
 * then moved onto each catalog's group, so their storefront is restricted and re-priced; the
 * rest keep the full catalogue. Catalogs are tagged "(Demo)" so the set can be cleaned in
 * isolation.
 */
class DemoCompanyCatalogSeeder extends DemoSeeder
{
    /**
     * Marker appended to demo catalog names so the set can be located and removed in isolation.
     */
    public const DEMO_TAG = '(Demo)';

    /**
     * Fraction of demo companies placed on a catalog (the remainder see the full storefront).
     */
    protected const ASSIGNED_FRACTION = 0.6;

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(array $parameters = [])
    {
        $companyIds = $this->demoCompanyIds()->shuffle()->values();

        if ($companyIds->isEmpty()) {
            return;
        }

        $now = $parameters['now'] ?? now()->toDateTimeString();
        $adminId = DB::table('admins')->orderBy('id')->value('id');
        $helper = app(CompanyCatalog::class);
        $repository = app(CompanyCatalogRepository::class);

        /**
         * Allowlist-eligible products: standalone simples + configurable parents (the products
         * shown individually in the storefront). Their priceable leaves are resolved per catalog.
         */
        $pool = DB::table('products')
            ->whereNull('parent_id')
            ->whereIn('type', ['simple', 'configurable'])
            ->pluck('id');

        if ($pool->isEmpty()) {
            return;
        }

        $specs = $this->catalogSpecs();

        $assignable = $companyIds->take((int) ceil($companyIds->count() * self::ASSIGNED_FRACTION));
        $buckets = $this->distribute($assignable, count($specs));

        foreach ($specs as $index => $spec) {
            $catalog = $repository->create([
                'name' => $spec['name'],
                'description' => $spec['description'],
                'status' => $spec['status'],
                'created_by' => $adminId,
            ]);

            $productIds = $pool->shuffle()->take(min($spec['products'], $pool->count()))->values()->all();

            $helper->syncProducts($catalog, $productIds);
            $helper->deriveCategories($catalog);
            $helper->setPrices($catalog, $this->buildPrices($helper, $catalog->fresh(), $spec));

            $this->assignCompanies($catalog->fresh(), $buckets[$index] ?? [], $now);
        }
    }

    /**
     * The demo catalogs to create — a spread of breadth, discount depth and tier pricing.
     */
    protected function catalogSpecs(): array
    {
        return [
            ['name' => 'Premium Wholesale '.self::DEMO_TAG, 'description' => 'Negotiated wholesale pricing for our highest-volume trade partners.', 'status' => 1, 'products' => 30, 'discount' => 15, 'tiers' => true],
            ['name' => 'Standard B2B '.self::DEMO_TAG, 'description' => 'The standard trade catalogue and pricing for business accounts.', 'status' => 1, 'products' => 24, 'discount' => 10, 'tiers' => true],
            ['name' => 'Essentials '.self::DEMO_TAG, 'description' => 'A curated set of everyday essentials at a modest trade discount.', 'status' => 1, 'products' => 14, 'discount' => 7, 'tiers' => false],
            ['name' => 'Seasonal Clearance '.self::DEMO_TAG, 'description' => 'End-of-season lines offered to trade accounts at clearance pricing.', 'status' => 1, 'products' => 18, 'discount' => 20, 'tiers' => false],
        ];
    }

    /**
     * Build the per-leaf discount price payload for a catalog, adding volume tiers where the
     * spec asks for them (deeper discounts as the quantity rises).
     */
    protected function buildPrices(CompanyCatalog $helper, $catalog, array $spec): array
    {
        $prices = [];

        foreach ($catalog->products as $product) {
            foreach ($helper->leafProducts($product) as $leaf) {
                $row = ['type' => 'discount', 'value' => $spec['discount']];

                if ($spec['tiers']) {
                    $row['breaks'] = [
                        ['qty' => 10, 'type' => 'discount', 'value' => $spec['discount'] + 5],
                        ['qty' => 50, 'type' => 'discount', 'value' => $spec['discount'] + 10],
                    ];
                }

                $prices[$leaf->id] = $row;
            }
        }

        return $prices;
    }

    /**
     * Move the given companies (and their members) onto the catalog's customer group.
     */
    protected function assignCompanies($catalog, $companyIds, string $now): void
    {
        $companyIds = collect($companyIds)->all();

        if (empty($companyIds) || ! $catalog->customer_group_id) {
            return;
        }

        $groupId = $catalog->customer_group_id;

        DB::table('customers')
            ->whereIn('id', $companyIds)
            ->update(['company_catalog_id' => $catalog->id, 'customer_group_id' => $groupId, 'updated_at' => $now]);

        $memberIds = DB::table('b2b_customer_companies')
            ->whereIn('company_id', $companyIds)
            ->pluck('customer_id')
            ->unique()
            ->all();

        if (! empty($memberIds)) {
            DB::table('customers')
                ->whereIn('id', $memberIds)
                ->update(['customer_group_id' => $groupId, 'updated_at' => $now]);
        }
    }

    /**
     * Spread the companies across the catalogs round-robin.
     */
    protected function distribute($companies, int $count): array
    {
        $buckets = array_fill(0, $count, []);

        foreach (collect($companies)->values() as $index => $companyId) {
            $buckets[$index % $count][] = $companyId;
        }

        return $buckets;
    }
}
