<?php

namespace Webkul\B2BSuite\Helpers;

use Illuminate\Support\Collection;
use Webkul\B2BSuite\Repositories\CompanyCatalogRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Contracts\Product;
use Webkul\Product\Jobs\UpdateCreatePriceIndex as UpdateCreatePriceIndexJob;
use Webkul\Product\Repositories\ProductCustomerGroupPriceRepository;
use Webkul\Product\Repositories\ProductRepository;

class CompanyCatalog
{
    /**
     * Code prefix for the hidden customer group backing a catalog.
     */
    const GROUP_CODE_PREFIX = 'company_catalog_';

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerGroupRepository $customerGroupRepository,
        protected CustomerRepository $customerRepository,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected ProductCustomerGroupPriceRepository $productCustomerGroupPriceRepository,
        protected CompanyCatalogRepository $companyCatalogRepository,
    ) {}

    /**
     * Provision (once) the dedicated, hidden customer group that backs a catalog.
     * Catalog prices are stored against this group and resolved by the core price index.
     */
    public function provisionGroup($catalog)
    {
        if ($catalog->customer_group_id) {
            return $catalog->customerGroup;
        }

        $group = $this->customerGroupRepository->create([
            'code' => self::GROUP_CODE_PREFIX.$catalog->id,
            'name' => 'Company Catalog - '.$catalog->name,
            'is_user_defined' => 0,
        ]);

        $this->companyCatalogRepository->update(['customer_group_id' => $group->id], $catalog->id);

        return $group;
    }

    /**
     * The default customer group customers fall back to when unassigned.
     */
    public function defaultGroupId(): int
    {
        $group = $this->customerGroupRepository->findOneByField('code', 'general');

        return $group?->id ?? 1;
    }

    /**
     * Resolve the active company catalog backing a customer group (or null).
     * This is the fast path used by storefront visibility/pricing.
     */
    public function resolveByGroupId($groupId)
    {
        if (! $groupId) {
            return null;
        }

        return $this->companyCatalogRepository
            ->where('customer_group_id', $groupId)
            ->where('status', 1)
            ->first();
    }

    /**
     * Resolve the company catalog that applies to a customer (or null).
     */
    public function resolveForCustomer($customer)
    {
        if (! $customer) {
            return null;
        }

        return $this->resolveByGroupId($customer->customer_group_id);
    }

    /**
     * Sync the full set of companies assigned to a catalog.
     */
    public function assignCompanies($catalog, array $companyIds): void
    {
        $this->provisionGroup($catalog);

        $catalog = $this->companyCatalogRepository->find($catalog->id);

        $companyIds = array_map('intval', $companyIds);

        $current = $this->customerRepository
            ->findWhere(['company_catalog_id' => $catalog->id])
            ->pluck('id')
            ->toArray();

        foreach (array_diff($companyIds, $current) as $companyId) {
            $this->attachCompany($companyId, $catalog->id, $catalog->customer_group_id);
        }

        foreach (array_diff($current, $companyIds) as $companyId) {
            $this->detachCompany($companyId);
        }
    }

    /**
     * Assign a company (and its members) to a catalog's group.
     */
    public function attachCompany($companyId, $catalogId, $groupId): void
    {
        $company = $this->customerRepository->find($companyId);

        if (! $company) {
            return;
        }

        $company->company_catalog_id = $catalogId;
        $company->customer_group_id = $groupId;
        $company->save();

        $this->updateMembersGroup($companyId, $groupId);
    }

    /**
     * Detach a company (and its members), reverting them to the default group.
     */
    public function detachCompany($companyId): void
    {
        $company = $this->customerRepository->find($companyId);

        if (! $company) {
            return;
        }

        $defaultGroupId = $this->defaultGroupId();

        $company->company_catalog_id = null;
        $company->customer_group_id = $defaultGroupId;
        $company->save();

        $this->updateMembersGroup($companyId, $defaultGroupId);
    }

    /**
     * Push a group assignment down to all members of a company.
     */
    public function updateMembersGroup($companyId, $groupId): void
    {
        $company = $this->customerRepository->find($companyId);

        if (! $company) {
            return;
        }

        $company->members->each(
            fn ($member) => $this->customerRepository->update(['customer_group_id' => $groupId], $member->id)
        );
    }

    /**
     * Sync the catalog product allowlist (which products assigned companies can see).
     */
    public function syncProducts($catalog, array $productIds): void
    {
        $catalog->products()->sync(array_map('intval', array_filter($productIds)));
    }

    /**
     * Derive and persist the catalog's visible categories from its assigned products.
     * The set is the products' categories plus every ancestor (so the storefront tree
     * stays navigable). Called on save.
     */
    public function deriveCategories($catalog): void
    {
        $productIds = $catalog->products()->pluck('products.id')->toArray();

        $catalog->categories()->sync($this->buildCategoryData($productIds)['categoryIds']);
    }

    /**
     * Build the category tree (with rolled-up product counts) for a set of products —
     * used by the save-confirmation preview.
     */
    public function categoryTreeForProducts(array $productIds): array
    {
        return $this->buildCategoryData($productIds)['tree'];
    }

    /**
     * The category ids visible to a catalog (the derived set).
     */
    public function allowedCategoryIds($catalog): array
    {
        return $catalog->categories()->pluck('categories.id')->toArray();
    }

    /**
     * Resolve the price-bearing LEAF products for an assigned product.
     *
     * Catalog prices are customer-group prices, which the core price index only reads
     * at the sellable leaf level. So a composite product fans out to its children:
     *   - configurable -> variants
     *   - grouped      -> associated products
     *   - bundle       -> bundle-option products
     *   - simple/virtual/downloadable -> the product itself
     *   - booking      -> none (core never reads group prices for booking)
     *
     * @return Collection<int, Product>
     */
    public function leafProducts($product)
    {
        return match ($product->type) {
            'configurable' => $product->variants()->get(),

            'grouped' => $product->grouped_products()
                ->with('associated_product')
                ->get()
                ->map(fn ($row) => $row->associated_product)
                ->filter()
                ->unique('id')
                ->values(),

            'bundle' => $product->bundle_options()
                ->with('bundle_option_products.product')
                ->get()
                ->flatMap(fn ($option) => $option->bundle_option_products)
                ->map(fn ($row) => $row->product)
                ->filter()
                ->unique('id')
                ->values(),

            'booking' => collect(),

            default => collect([$product]),
        };
    }

    /**
     * Store per-catalog fixed prices against the backing group and reindex.
     *
     * $prices is keyed by product_id; an empty value clears any override but the
     * product is still reindexed so it gets a price-index row for the group.
     */
    public function setPrices($catalog, array $prices): void
    {
        $this->provisionGroup($catalog);

        $catalog = $this->companyCatalogRepository->find($catalog->id);

        if (! $groupId = $catalog->customer_group_id) {
            return;
        }

        /**
         * Products that currently carry a catalog-group price — they must be
         * reindexed after we wipe/rewrite so stale overrides don't linger.
         */
        $affected = $this->productCustomerGroupPriceRepository
            ->findWhere(['customer_group_id' => $groupId])
            ->pluck('product_id')
            ->toArray();

        $this->productCustomerGroupPriceRepository->deleteWhere(['customer_group_id' => $groupId]);

        foreach ($prices as $productId => $row) {
            $productId = (int) $productId;

            $affected[] = $productId;

            /**
             * A plain scalar (legacy payload) is treated as a flat base price.
             */
            if (! is_array($row)) {
                $row = ['value' => $row];
            }

            /**
             * Build the qty-keyed tier ladder for this leaf: the qty=1 base catalog price
             * plus any volume breaks (qty >= 2). Each tier becomes one
             * product_customer_group_prices row — core's getCustomerGroupPrice() resolves
             * the right one by cart quantity, and the PDP renders the qty>1 rows as offers.
             */
            $tiers = [];

            if ($tier = $this->normalizeTier(1, $row['type'] ?? null, $row['value'] ?? null)) {
                $tiers[1] = $tier;
            }

            foreach ($row['breaks'] ?? [] as $break) {
                $qty = (int) ($break['qty'] ?? 0);

                if ($qty < 2) {
                    continue;
                }

                if ($tier = $this->normalizeTier($qty, $break['type'] ?? null, $break['value'] ?? null)) {
                    $tiers[$qty] = $tier;
                }
            }

            foreach ($tiers as $tier) {
                $this->productCustomerGroupPriceRepository->create([
                    'qty' => $tier['qty'],
                    'value_type' => $tier['type'],
                    'value' => $tier['value'],
                    'product_id' => $productId,
                    'customer_group_id' => $groupId,
                    'unique_id' => implode('|', [$tier['qty'], $productId, $groupId]),
                ]);
            }
        }

        /**
         * Reindex the priced leaves AND the assigned parents. Grouped/bundle leaves are
         * independent simple products with no parent_id, so the composite parent's
         * min/max index can only be refreshed by reindexing the parent itself (which
         * recurses to its children during indexing).
         */
        $parentIds = $catalog->products()->pluck('products.id')->toArray();

        $this->reindex(array_merge($affected, $parentIds));
    }

    /**
     * Dispatch a price reindex for the given products.
     */
    public function reindex(array $productIds): void
    {
        $productIds = array_values(array_unique(array_filter($productIds)));

        if (empty($productIds)) {
            return;
        }

        UpdateCreatePriceIndexJob::dispatch($productIds);
    }

    /**
     * Tear down a catalog: revert assigned companies/members and drop the backing group.
     */
    public function cleanup($catalog): void
    {
        $companyIds = $this->customerRepository
            ->findWhere(['company_catalog_id' => $catalog->id])
            ->pluck('id')
            ->toArray();

        foreach ($companyIds as $companyId) {
            $this->detachCompany($companyId);
        }

        if ($catalog->customer_group_id) {
            $defaultGroupId = $this->defaultGroupId();

            $this->customerRepository
                ->findWhere(['customer_group_id' => $catalog->customer_group_id])
                ->each(fn ($customer) => $this->customerRepository->update(['customer_group_id' => $defaultGroupId], $customer->id));

            $this->customerGroupRepository->delete($catalog->customer_group_id);
        }
    }

    /**
     * Resolve, from a set of products, the visible category set (products' categories +
     * ancestors) and a display tree where each node carries a rolled-up product count
     * (products in that category OR any of its descendants). Root categories are kept in
     * the id set (the tree needs them) but excluded from the displayed nodes.
     *
     * @return array{categoryIds: array<int>, tree: array<int, array>}
     */
    protected function buildCategoryData(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter(array_map('intval', $productIds))));

        if (empty($productIds)) {
            return ['categoryIds' => [], 'tree' => []];
        }

        /**
         * Direct category -> the assigned products that sit in it. Category links live on
         * the (parent) product, which is exactly what the allowlist holds.
         */
        $directProducts = [];

        foreach ($this->productRepository->with('categories')->findWhereIn('id', $productIds) as $product) {
            foreach ($product->categories as $category) {
                $directProducts[$category->id][$product->id] = true;
            }
        }

        if (empty($directProducts)) {
            return ['categoryIds' => [], 'tree' => []];
        }

        $rolled = [];

        $allIds = [];

        $directCategories = $this->categoryRepository->findWhereIn('id', array_keys($directProducts));

        foreach ($directCategories as $category) {
            $chain = $category->ancestors->pluck('id')->push($category->id)->all();

            foreach ($chain as $categoryId) {
                $allIds[$categoryId] = true;

                foreach ($directProducts[$category->id] as $productId => $unused) {
                    $rolled[$categoryId][$productId] = true;
                }
            }
        }

        $allIds = array_map('intval', array_keys($allIds));

        $tree = $this->categoryRepository->findWhereIn('id', $allIds)
            ->filter(fn ($category) => $category->parent_id !== null)
            ->sortBy('_lft')
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name ?: '#'.$category->id,
                'parent_id' => (int) $category->parent_id,
                'count' => count($rolled[$category->id] ?? []),
            ])
            ->values()
            ->all();

        /**
         * Nesting depth relative to the displayed tree (roots are excluded), so the
         * dialog can indent rows without a recursive component.
         */
        $byId = collect($tree)->keyBy('id');

        foreach ($tree as &$node) {
            $depth = 0;
            $parentId = $node['parent_id'];

            while ($parentId && $byId->has($parentId)) {
                $depth++;
                $parentId = $byId[$parentId]['parent_id'];
            }

            $node['depth'] = $depth;
        }

        return ['categoryIds' => $allIds, 'tree' => $tree];
    }

    /**
     * Validate and shape a single price tier. Returns ['qty', 'type', 'value'] or null
     * when the value is empty/invalid (a discount must be 0-100, a flat price >= 0).
     */
    protected function normalizeTier(int $qty, $type, $value): ?array
    {
        $type = $type === 'discount' ? 'discount' : 'fixed';

        if (
            $value === ''
            || $value === null
            || ! is_numeric($value)
            || (float) $value < 0
        ) {
            return null;
        }

        if (
            $type === 'discount'
            && (float) $value > 100
        ) {
            return null;
        }

        return [
            'qty' => $qty,
            'type' => $type,
            'value' => (float) $value,
        ];
    }
}
