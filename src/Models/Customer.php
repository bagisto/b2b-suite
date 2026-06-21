<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\B2BSuite\Repositories\CompanyAttributeRepository;
use Webkul\Customer\Models\Customer as BaseCustomer;
use Webkul\User\Models\AdminProxy;

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
        'type',
        'company_role_id',
        'company_catalog_id',
        'sales_rep_id',
    ];

    /**
     * The companies that belong to the customer.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'b2b_customer_companies', 'customer_id', 'company_id')
            ->where('type', 'company');
    }

    /**
     * The member customers that belong to this company.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'b2b_customer_companies', 'company_id', 'customer_id')
            ->where('type', 'user');
    }

    /**
     * The company catalog assigned to the company.
     */
    public function companyCatalog(): BelongsTo
    {
        return $this->belongsTo(CompanyCatalogProxy::modelClass(), 'company_catalog_id');
    }

    /**
     * The admin user assigned as the company's sales representative / account manager.
     */
    public function salesRep(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'sales_rep_id');
    }

    /**
     * Get the customer's flat information.
     */
    public function company_flats(): HasMany
    {
        return $this->hasMany(CompanyFlatProxy::modelClass());
    }

    /**
     * Get all the attributes for the attribute groups.
     */
    public function custom_attributes()
    {
        return (CompanyAttributeProxy::modelClass())::query()
            ->join(
                'b2b_company_attribute_group_mappings',
                'b2b_company_attributes.id',
                '=',
                'b2b_company_attribute_group_mappings.company_attribute_id'
            )
            ->join(
                'b2b_company_attribute_groups',
                'b2b_company_attribute_groups.id',
                '=',
                'b2b_company_attribute_group_mappings.company_attribute_group_id'
            )
            ->select('b2b_company_attributes.*');
    }

    /**
     * Get the customer attribute values that owns the customer.
     */
    public function attribute_values(): HasMany
    {
        return $this->hasMany(CompanyAttributeValueProxy::modelClass(), 'customer_id');
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
     * Get all the attributes for the attribute groups.
     */
    public function customAttributes(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->custom_attributes()->get()
        );
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

        if ($parentValue !== null || ! isset($this->id)) {
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
            /**
             * If there's any error getting custom attributes, just return null
             */
            return null;
        }

        return null;
    }

    /**
     * Attributes to array.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        if (! isset($this->id)) {
            return $attributes;
        }

        try {
            $hiddenAttributes = $this->getHidden();
            $familyAttributes = $this->getAllCustomAttributes();

            foreach ($familyAttributes as $attribute) {
                if (in_array($attribute->code, $hiddenAttributes)) {
                    continue;
                }

                /**
                 * Don't override existing attributes with custom ones
                 */
                if (! array_key_exists($attribute->code, $attributes)) {
                    $attributes[$attribute->code] = $this->getCustomAttributeValue($attribute);
                }
            }
        } catch (\Exception $e) {
            /**
             * If there's any error, just return the base attributes
             */
        }

        return $attributes;
    }

    /**
     * Get a custom attribute value.
     */
    public function getCustomAttributeValue($attribute): mixed
    {
        if (! $attribute) {
            return null;
        }

        try {
            $locale = core()->getRequestedLocaleCodeInRequestedChannel();
            $channel = core()->getRequestedChannelCode();

            /**
             * Eager load attribute_values if not already loaded
             */
            if (! $this->relationLoaded('attribute_values')) {
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

                    if (! $attributeValue || empty($attributeValue->{$attribute->column_name})) {
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

                    if (! $attributeValue || empty($attributeValue->{$attribute->column_name})) {
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
     * The company's business name (from the company flat, current locale first), or null
     * when none has been set.
     */
    public function businessName(): ?string
    {
        $flat = $this->company_flats->firstWhere('locale', app()->getLocale())
            ?? $this->company_flats->first();

        return $flat?->business_name ?: null;
    }

    /**
     * The admin id that a non-super-admin (a sales rep) should be scoped to — they only
     * see data for companies they manage. Returns null for super-admins (role
     * permission_type "all") and when unauthenticated, meaning "no scoping / see all".
     */
    public static function salesRepScopeId(): ?int
    {
        $admin = auth()->guard('admin')->user();

        if (! $admin || optional($admin->role)->permission_type === 'all') {
            return null;
        }

        return $admin->id;
    }

    /**
     * Whether the current admin may access data belonging to the given company. Super-admins
     * always can; a sales rep only for the companies they manage. Used to guard direct-URL
     * access (datagrid scoping alone only hides listing rows).
     */
    public static function repCanAccessCompany(?int $companyId): bool
    {
        $repId = self::salesRepScopeId();

        if ($repId === null) {
            return true;
        }

        return $companyId
            && self::where('id', $companyId)->where('sales_rep_id', $repId)->exists();
    }

    /**
     * Whether the current admin may access the given (shared) company catalog. Super-admins
     * always can; a sales rep when the catalog is assigned to a company they manage — which
     * is exactly when it appears in their scoped catalog listing.
     */
    public static function repCanAccessCatalog(?int $catalogId): bool
    {
        $repId = self::salesRepScopeId();

        if ($repId === null) {
            return true;
        }

        return $catalogId
            && self::where('sales_rep_id', $repId)->where('company_catalog_id', $catalogId)->exists();
    }

    /**
     * Whether the current admin may EDIT/DELETE the given catalog. Super-admins always can;
     * any other admin only for catalogs they created. Admins who can merely view a shared
     * catalog (a company they manage is assigned) but did not create it get a read-only view.
     */
    public static function repCanEditCatalog(?int $catalogId): bool
    {
        if (self::salesRepScopeId() === null) {
            return true;
        }

        $adminId = auth()->guard('admin')->user()?->id;

        return $catalogId
            && $adminId
            && CompanyCatalogProxy::modelClass()::where('id', $catalogId)
                ->where('created_by', $adminId)
                ->exists();
    }
}
