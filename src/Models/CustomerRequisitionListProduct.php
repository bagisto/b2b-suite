<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Webkul\B2BSuite\Contracts\CustomerRequisitionListProduct as CustomerRequisitionListProductContract;
use Webkul\Product\Models\ProductProxy;

class CustomerRequisitionListProduct extends Model implements CustomerRequisitionListProductContract
{
    protected $table = 'customer_requisition_list_products';

    public $timestamps = false;

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

    public function product(): HasOne
    {
        return $this->hasOne(ProductProxy::modelClass(), 'id', 'product_id');
    }

    public function variant(): HasOne
    {
        return $this->hasOne(ProductProxy::modelClass(), 'id', 'variant_id');
    }
}
