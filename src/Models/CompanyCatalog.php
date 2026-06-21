<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\B2BSuite\Contracts\CompanyCatalog as CompanyCatalogContract;
use Webkul\Category\Models\CategoryProxy;
use Webkul\Customer\Models\CustomerGroupProxy;
use Webkul\Customer\Models\CustomerProxy;
use Webkul\Product\Models\ProductProxy;

class CompanyCatalog extends Model implements CompanyCatalogContract
{
    /**
     * Active status.
     */
    public const STATUS_ACTIVE = 1;

    /**
     * Inactive status.
     */
    public const STATUS_INACTIVE = 0;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_company_catalogs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'customer_group_id',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * The customer group that backs this catalog (holds catalog prices).
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroupProxy::modelClass(), 'customer_group_id');
    }

    /**
     * Products assigned to (visible in) the catalog.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductProxy::modelClass(),
            'b2b_company_catalog_products',
            'company_catalog_id',
            'product_id'
        );
    }

    /**
     * Catalog product mapping rows.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CompanyCatalogProductProxy::modelClass(), 'company_catalog_id');
    }

    /**
     * Companies assigned to this catalog.
     */
    public function companies(): HasMany
    {
        return $this->hasMany(CustomerProxy::modelClass(), 'company_catalog_id')
            ->where('type', 'company');
    }

    /**
     * Categories visible to the catalog — derived from the assigned products
     * (their categories plus all ancestors), used to filter the storefront tree.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            CategoryProxy::modelClass(),
            'b2b_company_catalog_categories',
            'company_catalog_id',
            'category_id'
        );
    }
}
