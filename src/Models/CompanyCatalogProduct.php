<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CompanyCatalogProduct as CompanyCatalogProductContract;
use Webkul\Product\Models\ProductProxy;

class CompanyCatalogProduct extends Model implements CompanyCatalogProductContract
{
    protected $table = 'company_catalog_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_catalog_id',
        'product_id',
    ];

    /**
     * The catalog this mapping belongs to.
     */
    public function companyCatalog(): BelongsTo
    {
        return $this->belongsTo(CompanyCatalogProxy::modelClass(), 'company_catalog_id');
    }

    /**
     * The mapped product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ProductProxy::modelClass(), 'product_id');
    }
}
