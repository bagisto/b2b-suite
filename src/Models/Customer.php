<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\B2BSuite\Repositories\CompanyAttributeRepository;
use Webkul\Customer\Models\Customer as BaseCustomer;

class Customer extends BaseCustomer
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'password',
        'api_token',
        'token',
        'customer_group_id',
        'channel_id',
        'subscribed_to_news_letter',
        'status',
        'is_verified',
        'is_suspended',
        'slug',
        'type',
        'company_role_id',
    ];

    /**
     * The companies that belong to the customer.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'customer_companies', 'customer_id', 'company_id')
            ->where('type', 'company');
    }

    /**
     * The customers that belong to the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function customers()
    {
        return $this->belongsToMany(self::class, 'customer_companies', 'company_id', 'customer_id')
            ->where('type', 'user');
    }

    /**
     * Get the customer's full address attribute.
     */
    public function fullAddress(): Attribute
    {
        $addressParts = array_filter([
            implode(', ', array_filter(explode(PHP_EOL, $this->address))),
            $this->city,
            $this->state,
            $this->postcode,
            $this->country ? "({$this->country})" : null,
        ]);

        return Attribute::make(
            get: fn () => implode(', ', $addressParts),
        );
    }

    /**
     * Get the customer's flat information.
     */
    public function customer_flats(): HasMany
    {
        return $this->hasMany(CustomerFlatProxy::modelClass());
    }

    /**
     * Get all the attributes for the attribute groups.
     */
    public function custom_attributes()
    {
        return (CompanyAttributeProxy::modelClass())::query()
            ->join(
                'company_attribute_group_mappings',
                'company_attributes.id',
                '=',
                'company_attribute_group_mappings.company_attribute_id'
            )
            ->join(
                'company_attribute_groups',
                'company_attribute_groups.id',
                '=',
                'company_attribute_group_mappings.company_attribute_group_id'
            )
            ->select('company_attributes.*');
    }

    /**
     * Get all the attributes for the attribute groups.
     */
    public function customAttributes(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->custom_attributes()->get()
        );
    }

    /**
     * Get the customer attribute values that owns the customer.
     */
    public function attribute_values(): HasMany
    {
        return $this->hasMany(CompanyAttributeValueProxy::modelClass(), 'customer_id');
    }

    /**
     * Get an attribute from the model.
     */
    public function getAttribute($key): mixed
    {

        if (
            ! method_exists(static::class, $key)
            && ! in_array($key, [
                'pivot',
                'parent_id',
            ])
            && ! isset($this->attributes[$key])
        ) {
            if (isset($this->id)) {
                $attribute = $this->getAllCustomAttributes()
                    ->where('code', $key)
                    ->first();

                $this->attributes[$key] = $this->getCustomAttributeValue($attribute);

                return $this->getAttributeValue($key);
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * Get an product attribute value.
     */
    public function getCustomAttributeValue($attribute): mixed
    {
        if (! $attribute) {
            return null;
        }

        $locale = core()->getRequestedLocaleCodeInRequestedChannel();

        $channel = core()->getRequestedChannelCode();

        if (empty($this->attribute_values->count())) {
            $this->load('attribute_values');
        }

        if ($attribute->value_per_channel) {
            if ($attribute->value_per_locale) {
                $attributeValue = $this->attribute_values
                    ->where('channel', $channel)
                    ->where('locale', $locale)
                    ->where('company_attribute_id', $attribute->id)
                    ->first();

                if (empty($attributeValue[$attribute->column_name])) {
                    $attributeValue = $this->attribute_values
                        ->where('channel', core()->getDefaultChannelCode())
                        ->where('locale', core()->getDefaultLocaleCodeFromDefaultChannel())
                        ->where('company_attribute_id', $attribute->id)
                        ->first();
                }
            } else {
                $attributeValue = $this->attribute_values
                    ->where('channel', $channel)
                    ->where('company_attribute_id', $attribute->id)
                    ->first();
            }
        } else {
            if ($attribute->value_per_locale) {
                $attributeValue = $this->attribute_values
                    ->where('locale', $locale)
                    ->where('company_attribute_id', $attribute->id)
                    ->first();

                if (empty($attributeValue[$attribute->column_name])) {
                    $attributeValue = $this->attribute_values
                        ->where('locale', core()->getDefaultLocaleCodeFromDefaultChannel())
                        ->where('company_attribute_id', $attribute->id)
                        ->first();
                }
            } else {
                $attributeValue = $this->attribute_values
                    ->where('company_attribute_id', $attribute->id)
                    ->first();
            }
        }

        return $attributeValue[$attribute->column_name] ?? $attribute->default_value;
    }

    /**
     * Check in all attributes.
     */
    public function getAllCustomAttributes(): object
    {
        static $allAttributes;

        if ($allAttributes) {
            return $allAttributes;
        }

        $allAttributes = core()->getSingletonInstance(CompanyAttributeRepository::class)->all();

        return $allAttributes;
    }

    /**
     * Check in all attributes.
     */
    public function checkInAllAttributes(): object
    {
        static $allAttributes;

        if ($allAttributes) {
            return $allAttributes;
        }

        $allAttributes = core()->getSingletonInstance(CompanyAttributeRepository::class)->all();

        return $allAttributes;
    }

    /**
     * Attributes to array.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        $hiddenAttributes = $this->getHidden();

        if (isset($this->id)) {
            $familyAttributes = $this->getAllCustomAttributes();

            foreach ($familyAttributes as $attribute) {
                if (in_array($attribute->code, $hiddenAttributes)) {
                    continue;
                }

                $attributes[$attribute->code] = $this->getCustomAttributeValue($attribute);
            }
        }

        return $attributes;
    }
}
