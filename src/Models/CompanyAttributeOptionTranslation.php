<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\B2BSuite\Contracts\CompanyAttributeOptionTranslation as AttributeOptionTranslationContract;

class CompanyAttributeOptionTranslation extends Model implements AttributeOptionTranslationContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_company_attribute_option_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['label'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
}
