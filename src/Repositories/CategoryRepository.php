<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Helpers\CompanyCatalog as CompanyCatalogHelper;
use Webkul\Category\Repositories\CategoryRepository as BaseCategoryRepository;

/**
 * Restricts the storefront category tree (and category pages) to a company catalog's
 * derived categories. When the current customer's group is backed by an active catalog,
 * only that catalog's categories are visible; everything else is hidden / 404s.
 *
 * No effect for guests, admins, or customers not assigned to a catalog.
 */
class CategoryRepository extends BaseCategoryRepository
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
     * Cached allowed category ids.
     */
    protected ?array $allowedIds = null;

    /**
     * {@inheritdoc}
     */
    public function getVisibleCategoryTree($id = null)
    {
        if (! $this->companyCatalog()) {
            return parent::getVisibleCategoryTree($id);
        }

        $allowed = $this->allowedCategoryIds();

        return $id
            ? $this->model::orderBy('position', 'ASC')->where('status', 1)->whereIn('id', $allowed)->descendantsAndSelf($id)->toTree($id)
            : $this->model::orderBy('position', 'ASC')->where('status', 1)->whereIn('id', $allowed)->get()->toTree();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug($slug)
    {
        $category = parent::findBySlug($slug);

        if (
            $category
            && $this->companyCatalog()
            && ! in_array($category->id, $this->allowedCategoryIds())
        ) {
            return null;
        }

        return $category;
    }

    /**
     * Resolve the active company catalog for the current storefront customer (or null).
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
         * Category filtering is a STOREFRONT concern only — never restrict the admin
         * (e.g. the category tree in catalog management). Gate on the REQUEST being an
         * admin request, not on whether an admin session merely exists.
         */
        if ($this->isAdminRequest()) {
            return $this->catalog = null;
        }

        $groupId = auth()->guard('customer')->user()?->customer_group_id;

        if (! $groupId) {
            return $this->catalog = null;
        }

        return $this->catalog = app(CompanyCatalogHelper::class)->resolveByGroupId($groupId);
    }

    /**
     * The category ids the current catalog allows (empty when none).
     */
    protected function allowedCategoryIds(): array
    {
        if ($this->allowedIds !== null) {
            return $this->allowedIds;
        }

        $catalog = $this->companyCatalog();

        return $this->allowedIds = $catalog
            ? app(CompanyCatalogHelper::class)->allowedCategoryIds($catalog)
            : [];
    }

    /**
     * Whether the current request targets the admin area.
     */
    protected function isAdminRequest(): bool
    {
        if (request()->routeIs('admin.*')) {
            return true;
        }

        $adminPrefix = trim((string) config('app.admin_url', 'admin'), '/') ?: 'admin';

        return request()->is($adminPrefix) || request()->is($adminPrefix.'/*');
    }
}
