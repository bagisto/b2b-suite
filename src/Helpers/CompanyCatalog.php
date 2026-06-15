<?php

namespace Webkul\B2BSuite\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\B2BSuite\Repositories\CompanyCatalogRepository;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Product\Contracts\Product;
use Webkul\Product\Jobs\UpdateCreatePriceIndex as UpdateCreatePriceIndexJob;
use Webkul\Product\Models\ProductCustomerGroupPriceProxy;

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
        protected CompanyCatalogRepository $companyCatalogRepository,
        protected CustomerGroupRepository $customerGroupRepository,
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

        $current = CustomerProxy::modelClass()::query()
            ->where('company_catalog_id', $catalog->id)
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
        $company = CustomerProxy::modelClass()::find($companyId);

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
        $company = CustomerProxy::modelClass()::find($companyId);

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
        $memberIds = DB::table('customer_companies')
            ->where('company_id', $companyId)
            ->pluck('customer_id')
            ->toArray();

        if (empty($memberIds)) {
            return;
        }

        CustomerProxy::modelClass()::query()
            ->whereIn('id', $memberIds)
            ->update(['customer_group_id' => $groupId]);
    }

    /**
     * Sync the catalog product allowlist (which products assigned companies can see).
     */
    public function syncProducts($catalog, array $productIds): void
    {
        $catalog->products()->sync(array_map('intval', array_filter($productIds)));
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

        $model = ProductCustomerGroupPriceProxy::modelClass();

        /**
         * Products that currently carry a catalog-group price — they must be
         * reindexed after we wipe/rewrite so stale overrides don't linger.
         */
        $affected = $model::query()
            ->where('customer_group_id', $groupId)
            ->pluck('product_id')
            ->toArray();

        $model::query()->where('customer_group_id', $groupId)->delete();

        foreach ($prices as $productId => $row) {
            $productId = (int) $productId;

            $affected[] = $productId;

            /**
             * Each row is ['type' => 'fixed'|'discount', 'value' => number]. A plain
             * scalar (legacy) is treated as a flat price.
             */
            $value = is_array($row) ? ($row['value'] ?? null) : $row;

            $type = (is_array($row) && ($row['type'] ?? null) === 'discount')
                ? 'discount'
                : 'fixed';

            if (
                $value === ''
                || $value === null
                || ! is_numeric($value)
                || (float) $value < 0
            ) {
                continue;
            }

            /**
             * A discount is a percentage off the base price (core clamps to 0-100).
             */
            if (
                $type === 'discount'
                && (float) $value > 100
            ) {
                continue;
            }

            $model::create([
                'qty' => 1,
                'value_type' => $type,
                'value' => (float) $value,
                'product_id' => $productId,
                'customer_group_id' => $groupId,
                'unique_id' => implode('|', [1, $productId, $groupId]),
            ]);
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
        $companyIds = CustomerProxy::modelClass()::query()
            ->where('company_catalog_id', $catalog->id)
            ->pluck('id')
            ->toArray();

        foreach ($companyIds as $companyId) {
            $this->detachCompany($companyId);
        }

        if ($catalog->customer_group_id) {
            $defaultGroupId = $this->defaultGroupId();

            CustomerProxy::modelClass()::query()
                ->where('customer_group_id', $catalog->customer_group_id)
                ->update(['customer_group_id' => $defaultGroupId]);

            $this->customerGroupRepository->delete($catalog->customer_group_id);
        }
    }
}
