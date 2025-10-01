<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\B2BSuite\Contracts\CustomerRequisitionList as CustomerRequisitionListContract;
use Webkul\Product\Models\ProductProxy;

class CustomerRequisitionList extends Model implements CustomerRequisitionListContract
{
    protected $table = 'customer_requisition_lists';

    /**
     * Active status.
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * Inactive status.
     */
    public const STATUS_INACTIVE = 'inactive';

    /**
     * Yes status.
     */
    public const STATUS_YES = 1;

    /**
     * No status.
     */
    public const STATUS_NO = 0;

    protected $fillable = [
        'name',
        'description',
        'status',
        'is_default',
        'company_id',
        'customer_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductProxy::modelClass(),
            'customer_requisition_list_products',
            'requisition_list_id',
            'product_id'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerRequisitionListProductProxy::modelClass(), 'requisition_list_id');
    }
}
