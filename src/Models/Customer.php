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
            implode(', ', array_filter(explode(PHP_EOL, $this->address ?? ''))),
            $this->city ?? '',
            $this->state ?? '',
            $this->postcode ?? '',
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
        if (in_array($key, ['pivot', 'parent_id'])) {
            return parent::getAttribute($key);
        }

        if (method_exists(static::class, $key)) {
            return parent::getAttribute($key);
        }

        if (array_key_exists($key, $this->attributes)) {
            return parent::getAttribute($key);
        }

        if (array_key_exists($key, $this->relations)) {
            return parent::getAttribute($key);
        }

        $parentValue = parent::getAttribute($key);
        
        if ($parentValue !== null || !isset($this->id)) {
            return $parentValue;
        }

        try {
            $attribute = $this->getAllCustomAttributes()
                ->where('code', $key)
                ->first();

            if ($attribute) {
                $customValue = $this->getCustomAttributeValue($attribute);
                $this->attributes[$key] = $customValue;
                return $customValue;
            }
        } catch (\Exception $e) {
            // If there's any error getting custom attributes, just return null
            return null;
        }

        return null;
    }

    /**
     * Get a custom attribute value.
     */
    public function getCustomAttributeValue($attribute): mixed
    {
        if (!$attribute) {
            return null;
        }

        try {
            $locale = core()->getRequestedLocaleCodeInRequestedChannel();
            $channel = core()->getRequestedChannelCode();

            // Eager load attribute_values if not already loaded
            if (!$this->relationLoaded('attribute_values')) {
                $this->load('attribute_values');
            }

            $attributeValue = null;

            if ($attribute->value_per_channel) {
                if ($attribute->value_per_locale) {
                    $attributeValue = $this->attribute_values
                        ->where('channel', $channel)
                        ->where('locale', $locale)
                        ->where('company_attribute_id', $attribute->id)
                        ->first();

                    if (!$attributeValue || empty($attributeValue->{$attribute->column_name})) {
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

                    if (!$attributeValue || empty($attributeValue->{$attribute->column_name})) {
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

            return $attributeValue->{$attribute->column_name} ?? $attribute->default_value ?? null;
        } catch (\Exception $e) {
            return $attribute->default_value ?? null;
        }
    }

    /**
     * Get all custom attributes.
     */
    public function getAllCustomAttributes(): object
    {
        static $allAttributes;

        if ($allAttributes) {
            return $allAttributes;
        }

        try {
            $allAttributes = core()->getSingletonInstance(CompanyAttributeRepository::class)->all();
        } catch (\Exception $e) {
            $allAttributes = collect([]);
        }

        return $allAttributes;
    }

    /**
     * Check in all attributes.
     */
    public function checkInAllAttributes(): object
    {
        return $this->getAllCustomAttributes();
    }

    /**
     * Attributes to array.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        if (!isset($this->id)) {
            return $attributes;
        }

        try {
            $hiddenAttributes = $this->getHidden();
            $familyAttributes = $this->getAllCustomAttributes();

            foreach ($familyAttributes as $attribute) {
                if (in_array($attribute->code, $hiddenAttributes)) {
                    continue;
                }

                // Don't override existing attributes with custom ones
                if (!array_key_exists($attribute->code, $attributes)) {
                    $attributes[$attribute->code] = $this->getCustomAttributeValue($attribute);
                }
            }
        } catch (\Exception $e) {
            // If there's any error, just return the base attributes
        }

        return $attributes;
    }
}