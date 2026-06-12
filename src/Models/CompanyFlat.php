<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CompanyFlat as CompanyFlatContract;
use Webkul\Customer\Models\CustomerProxy;

class CompanyFlat extends Model implements CompanyFlatContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'company_flat';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the customer that owns the flat.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}
