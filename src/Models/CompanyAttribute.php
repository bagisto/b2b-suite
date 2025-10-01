<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\B2BSuite\Contracts\CompanyAttribute as AttributeContract;
use Webkul\Core\Eloquent\TranslatableModel;

class CompanyAttribute extends TranslatableModel implements AttributeContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'admin_name',
        'type',
        'validation',
        'regex',
        'position',
        'is_required',
        'is_unique',
        'is_user_defined',
        'is_visible_on_sign_up',
        'default_value',
        'value_per_locale',
        'value_per_channel',
        'enable_editor',
    ];

    /**
     * The attributes that should be translated.
     *
     * @var array
     */
    public $translatedAttributes = ['name'];

    /**
     * Attribute type fields.
     *
     * @var array
     */
    public $attributeTypeFields = [
        'text'        => 'text_value',
        'textarea'    => 'text_value',
        'price'       => 'float_value',
        'boolean'     => 'boolean_value',
        'select'      => 'integer_value',
        'multiselect' => 'text_value',
        'datetime'    => 'datetime_value',
        'date'        => 'date_value',
        'file'        => 'text_value',
        'image'       => 'text_value',
        'checkbox'    => 'text_value',
    ];

    /**
     * Get the options.
     */
    public function options(): HasMany
    {
        return $this->hasMany(CompanyAttributeOptionProxy::modelClass());
    }

    /**
     * Returns attribute value table column based attribute type
     */
    protected function columnName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->attributeTypeFields[$this->type],
        );
    }

    /**
     * Get the validation attribute
     */
    protected function validations(): Attribute
    {
        $validations = [];

        if ($this->is_required) {
            $validations[] = 'required: true';
        }

        if ($this->type == 'price') {
            $validations[] = 'decimal: true';
        }

        if ($this->type == 'file') {
            $retVal = '2048';

            if ($retVal) {
                $validations[] = 'size:'.$retVal;
            }
        }

        if ($this->type == 'image') {
            $retVal = '2048';

            if ($retVal) {
                $validations[] = 'size:'.$retVal.', mimes: ["image/bmp", "image/jpeg", "image/jpg", "image/png", "image/webp"]';
            }
        }

        if ($this->validation == 'regex') {
            $validations[] = 'regex: '.$this->regex;
        } elseif ($this->validation) {
            $validations[] = $this->validation.': true';
        }

        return Attribute::make(
            get: fn () => '{ '.implode(', ', array_filter($validations)).' }',
        );
    }

    /**
     * Get the attributes that owns the attribute group.
     */
    public function attribute_group(): BelongsToMany
    {
        return $this->belongsToMany(CompanyAttributeGroupProxy::modelClass(), 'company_attribute_group_mappings')
            ->withPivot('position')
            ->orderBy('pivot_position', 'asc');
    }
}
