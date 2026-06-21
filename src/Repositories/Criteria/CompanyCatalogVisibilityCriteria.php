<?php

namespace Webkul\B2BSuite\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class CompanyCatalogVisibilityCriteria implements CriteriaInterface
{
    /**
     * Create a new criteria instance.
     *
     * @return void
     */
    public function __construct(protected int $catalogId) {}

    /**
     * Restrict the product query to the catalog's allowlist.
     *
     * @param  Builder  $model
     * @return Builder
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $catalogId = $this->catalogId;

        return $model->whereIn('products.id', function ($query) use ($catalogId) {
            $query->select('product_id')
                ->from('b2b_company_catalog_products')
                ->where('company_catalog_id', $catalogId);
        });
    }
}
