<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CustomerFlat as CustomerFlatContract;
use Webkul\Customer\Models\CustomerProxy;

class CustomerFlat extends Model implements CustomerFlatContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_flat';

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

    // /**
    //  * The attributes that are mass assignable.
    //  *
    //  * @var array
    //  */
    // protected $fillable = [
    //     'customer_id',
    //     'first_name',
    //     'last_name',
    //     'email',
    //     'phone',
    //     'slug',
    //     'business_name',
    //     'website_url',
    //     'vat_tax_id',
    //     'address',
    //     'state',
    //     'city',
    //     'country',
    //     'postcode',
    //     'meta_title',
    //     'meta_description',
    //     'meta_keywords',
    //     'return_policy',
    //     'shipping_policy',
    //     'privacy_policy',
    //     'facebook',
    //     'instagram',
    //     'youtube',
    //     'locale',
    //     'channel',
    // ];

    /**
     * Get the customer that owns the flat.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }
}
