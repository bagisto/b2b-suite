<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Webkul\B2BSuite\Contracts\CustomerRequisitionListProduct as CustomerRequisitionListProductContract;
use Webkul\Product\Models\ProductProxy;

class CustomerRequisitionListProduct extends Model implements CustomerRequisitionListProductContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_customer_requisition_list_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'requisition_list_id',
        'product_id',
        'variant_id',
        'type',
        'sku',
        'name',
        'qty',
        'price',
        'base_price',
        'total',
        'base_total',
        'additional',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The product this line references.
     */
    public function product(): HasOne
    {
        return $this->hasOne(ProductProxy::modelClass(), 'id', 'product_id');
    }

    /**
     * The variant this line references.
     */
    public function variant(): HasOne
    {
        return $this->hasOne(ProductProxy::modelClass(), 'id', 'variant_id');
    }
}
