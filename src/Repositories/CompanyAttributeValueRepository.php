<?php

namespace Webkul\B2BSuite\Repositories;

use Illuminate\Support\Facades\Storage;
use Webkul\B2BSuite\Models\CompanyAttributeValue;
use Webkul\Core\Eloquent\Repository;

class CompanyAttributeValueRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return CompanyAttributeValue::class;
    }

    /**
     * Save attribute values
     *
     * @param  array  $data
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @param  mixed  $attributes
     * @return void
     */
    public function saveValues($data, $customer, $attributes)
    {
        $attributeValuesToInsert = [];

        foreach ($attributes as $attribute) {
            if ($attribute->type === 'boolean') {
                $data[$attribute->code] = ! empty($data[$attribute->code]);
            }

            if (in_array($attribute->type, ['multiselect', 'checkbox'])) {
                $data[$attribute->code] = implode(',', $data[$attribute->code] ?? []);
            }

            if (! isset($data[$attribute->code])) {
                continue;
            }

            if (
                $attribute->type === 'price'
                && empty($data[$attribute->code])
            ) {
                $data[$attribute->code] = null;
            }

            if (
                $attribute->type === 'date'
                && empty($data[$attribute->code])
            ) {
                $data[$attribute->code] = null;
            }

            if (in_array($attribute->type, ['image', 'file'])) {
                $data[$attribute->code] = gettype($data[$attribute->code]) === 'object'
                    ? request()->file($attribute->code)->store('company/'.$customer->id)
                    : $data[$attribute->code];
            }

            $attributeValues = $customer->attribute_values
                ->where('company_attribute_id', $attribute->id);

            $channel = $attribute->value_per_channel ? ($data['channel'] ?? core()->getDefaultChannelCode()) : null;

            $locale = $attribute->value_per_locale ? ($data['locale'] ?? core()->getDefaultLocaleCodeFromDefaultChannel()) : null;

            if ($attribute->value_per_channel) {
                if ($attribute->value_per_locale) {
                    $filteredAttributeValues = $attributeValues
                        ->where('channel', $channel)
                        ->where('locale', $locale);
                } else {
                    $filteredAttributeValues = $attributeValues
                        ->where('channel', $channel);
                }
            } else {
                if ($attribute->value_per_locale) {
                    $filteredAttributeValues = $attributeValues
                        ->where('locale', $locale);
                } else {
                    $filteredAttributeValues = $attributeValues;
                }
            }

            $attributeValue = $filteredAttributeValues->first();

            $uniqueId = implode('|', array_filter([
                $channel,
                $locale,
                $customer->id,
                $attribute->id,
            ]));

            if (! $attributeValue) {
                $attributeValuesToInsert[] = array_merge($this->getAttributeTypeColumnValues($attribute, $data[$attribute->code]), [
                    'customer_id'              => $customer->id,
                    'company_attribute_id'     => $attribute->id,
                    'channel'                  => $channel,
                    'locale'                   => $locale,
                    'unique_id'                => $uniqueId,
                ]);
            } else {
                $previousTextValue = $attributeValue->text_value;

                if (in_array($attribute->type, ['image', 'file'])) {
                    /**
                     * If $data[$attribute->code]['delete'] is not empty, that means someone selected the "delete" option.
                     */
                    if (! empty($data[$attribute->code]['delete'])) {
                        Storage::delete($previousTextValue);

                        $data[$attribute->code] = null;
                    }
                    /**
                     * If $data[$attribute->code] is not equal to the previous one, that means someone has
                     * updated the file or image. In that case, we will remove the previous file.
                     */
                    elseif (
                        ! empty($previousTextValue)
                        && $data[$attribute->code] != $previousTextValue
                    ) {
                        Storage::delete($previousTextValue);
                    }
                }

                $attributeValue = $this->update([
                    $attribute->column_name => $data[$attribute->code],
                    'unique_id'             => $uniqueId,
                ], $attributeValue->id);
            }
        }

        if (! empty($attributeValuesToInsert)) {
            $this->insert($attributeValuesToInsert);
        }
    }

    /**
     * Get attribute type column values.
     */
    public function getAttributeTypeColumnValues($attribute, $value)
    {
        $attributeTypeFields = array_fill_keys(array_values($attribute->attributeTypeFields), null);

        $attributeTypeFields[$attribute->column_name] = $value;

        return $attributeTypeFields;
    }

    /**
     * Check if the attribute value is unique for the customer.
     */
    public function isValueUnique(
        int $customerId,
        int $attributeId,
        string $column,
        mixed $value
    ): bool {
        $count = $this->resetScope()
            ->model
            ->where($column, $value)
            ->where('company_attribute_id', '=', $attributeId)
            ->where('customer_id', '!=', $customerId)
            ->count('id');

        return ! $count;
    }
}
