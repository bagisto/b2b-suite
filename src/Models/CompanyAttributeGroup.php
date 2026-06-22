<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Webkul\B2BSuite\Contracts\CompanyAttributeGroup as CompanyAttributeGroupContract;
use Webkul\Core\Eloquent\TranslatableModel;

class CompanyAttributeGroup extends TranslatableModel implements CompanyAttributeGroupContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_company_attribute_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'admin_name',
        'column',
        'position',
        'is_user_defined',
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
    public $translatedAttributes = ['name'];

    /**
     * Get the attributes that owns the attribute group.
     */
    public function custom_attributes(): BelongsToMany
    {
        return $this->belongsToMany(CompanyAttributeProxy::modelClass(), 'b2b_company_attribute_group_mappings')
            ->withPivot('position')
            ->orderBy('pivot_position', 'asc');
    }
}
