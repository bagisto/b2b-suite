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

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_customer_requisition_lists';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'is_default',
        'company_id',
        'customer_id',
    ];

    /**
     * The company that owns the requisition list.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'company_id');
    }

    /**
     * The customer that owns the requisition list.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * The products belonging to the requisition list.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductProxy::modelClass(),
            'b2b_customer_requisition_list_products',
            'requisition_list_id',
            'product_id'
        );
    }

    /**
     * The line items belonging to the requisition list.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CustomerRequisitionListProductProxy::modelClass(), 'requisition_list_id');
    }
}
