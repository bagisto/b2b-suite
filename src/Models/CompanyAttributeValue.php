<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CompanyAttributeValue as CompanyAttributeValueContract;
use Webkul\Customer\Models\CustomerProxy;

class CompanyAttributeValue extends Model implements CompanyAttributeValueContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_attribute_values';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'locale',
        'channel',
        'text_value',
        'boolean_value',
        'integer_value',
        'float_value',
        'datetime_value',
        'date_value',
        'json_value',
        'customer_id',
        'company_attribute_id',
        'unique_id',
    ];

    /**
     * Get the attribute that owns the attribute value.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(CompanyAttributeProxy::modelClass());
    }

    /**
     * Get the company that owns the attribute value.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass());
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
