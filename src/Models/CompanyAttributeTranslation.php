<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\B2BSuite\Contracts\CompanyAttributeTranslation as AttributeTranslationContract;

class CompanyAttributeTranslation extends Model implements AttributeTranslationContract
{
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
