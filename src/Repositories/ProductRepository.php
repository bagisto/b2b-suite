<?php

namespace Webkul\B2BSuite\Repositories;

use Illuminate\Support\Facades\DB;
use Webkul\B2BSuite\Helpers\CompanyCatalog as CompanyCatalogHelper;
use Webkul\B2BSuite\Repositories\Criteria\CompanyCatalogVisibilityCriteria;
use Webkul\Product\Contracts\Product;
use Webkul\Product\Repositories\ProductRepository as BaseProductRepository;

/**
 * Enforces company-catalog "allowlist" visibility on the storefront. When the
 * current customer's group is backed by an active company catalog, product
 * listing/search/PDP are restricted to that catalog's assigned products.
 *
 * No effect for guests, admins, or customers not assigned to a catalog.
 */
class ProductRepository extends BaseProductRepository
{
    /**
     * Resolved catalog cache for the current request/instance.
     */
    protected $catalog = null;

    /**
     * Whether the catalog has been resolved yet.
     */
    protected bool $catalogResolved = false;

    /**
     * Whether the visibility criterion has been pushed.
     */
    protected bool $catalogCriteriaApplied = false;

    /**
     * Resolve the active company catalog for the current customer group (or null).
     */
    protected function companyCatalog()
    {
        if ($this->catalogResolved) {
            return $this->catalog;
        }

        $this->catalogResolved = true;

        if (! (bool) core()->getConfigData('b2b.general.settings.active')) {
            return $this->catalog = null;
        }

        /**
         * Catalog visibility is a STOREFRONT concern only. The admin panel shares the
         * browser session with the shop, so a logged-in customer would otherwise leak
         * their catalog filter into admin product listings (e.g. the assign-products
         * modal). Never restrict products when an admin is authenticated.
         */
        if (auth()->guard('admin')->check()) {
            return $this->catalog = null;
        }

        $groupId = $this->customerRepository->getCurrentGroup()?->id;

        return $this->catalog = app(CompanyCatalogHelper::class)->resolveByGroupId($groupId);
    }

    /**
     * Push the allowlist criterion once when a catalog applies.
     */
    protected function applyCatalogVisibility(): void
    {
        if ($this->catalogCriteriaApplied) {
            return;
        }

        $catalog = $this->companyCatalog();

        if (! $catalog) {
            return;
        }

        $this->pushCriteria(new CompanyCatalogVisibilityCriteria($catalog->id));

        $this->catalogCriteriaApplied = true;
    }

    /**
     * Check whether a product is within the current customer's catalog.
     */
    protected function isVisible($product): bool
    {
        $catalog = $this->companyCatalog();

        if (! $catalog) {
            return true;
        }

        $productId = $product->parent_id ?? $product->id;

        return DB::table('company_catalog_products')
            ->where('company_catalog_id', $catalog->id)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * {@inheritdoc}
     */
    public function getAll(array $params = [])
    {
        $this->applyCatalogVisibility();

        return parent::getAll($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPrice($params = [])
    {
        $this->applyCatalogVisibility();

        return parent::getMaxPrice($params);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug(string $slug): ?Product
    {
        $product = parent::findBySlug($slug);

        if (
            $product
            && ! $this->isVisible($product)
        ) {
            return null;
        }

        return $product;
    }
}
