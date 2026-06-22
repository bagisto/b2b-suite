<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\B2BSuite\Contracts\CompanyAttributeGroupTranslation as CompanyAttributeGroupTranslationContract;

class CompanyAttributeGroupTranslation extends Model implements CompanyAttributeGroupTranslationContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_company_attribute_group_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
