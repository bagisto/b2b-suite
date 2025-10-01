<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CompanyAttributeOption as AttributeOptionContract;
use Webkul\Core\Eloquent\TranslatableModel;

class CompanyAttributeOption extends TranslatableModel implements AttributeOptionContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_name',
        'swatch_value',
        'sort_order',
        'company_attribute_id',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be translated.
     *
     * @var array
     */
    public $translatedAttributes = ['label'];

    /**
     * Get the attribute that owns the attribute option.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(CompanyAttributeProxy::modelClass());
    }
}
