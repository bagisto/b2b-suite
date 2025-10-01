<?php

namespace Webkul\B2BSuite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyAttributeTableSeeder extends Seeder
{
    public function run(array $parameters = [])
    {
        DB::table('company_attributes')->truncate();

        DB::table('company_attribute_translations')->truncate();

        $attributes = [
            [
                'id'                    => 1,
                'code'                  => 'first_name',
                'type'                  => 'text',
                'is_required'           => 1,
                'is_visible_on_sign_up' => 1,
                'value_per_locale'      => 1,
            ],
            [
                'id'                    => 2,
                'code'                  => 'last_name',
                'type'                  => 'text',
                'is_required'           => 1,
                'is_visible_on_sign_up' => 1,
                'value_per_locale'      => 1,
            ],
            [
                'id'                    => 3,
                'code'                  => 'email',
                'type'                  => 'text',
                'validation'            => 'email',
                'is_required'           => 1,
                'is_unique'             => 1,
                'is_visible_on_sign_up' => 1,
            ],
            [
                'id'                    => 4,
                'code'                  => 'phone',
                'type'                  => 'text',
                'validation'            => 'phone',
                'is_required'           => 1,
                'is_unique'             => 1,
                'is_visible_on_sign_up' => 1,
            ],
            [
                'id'                    => 5,
                'code'                  => 'slug',
                'type'                  => 'text',
                'is_required'           => 1,
                'is_unique'             => 1,
                'is_visible_on_sign_up' => 1,
            ],
            [
                'id'                    => 6,
                'code'                  => 'business_name',
                'type'                  => 'text',
                'is_required'           => 1,
                'is_unique'             => 1,
                'is_visible_on_sign_up' => 1,
                'value_per_locale'      => 1,
            ],
            [
                'id'                    => 7,
                'code'                  => 'website_url',
                'type'                  => 'text',
                'is_required'           => 0,
                'is_unique'             => 1,
            ],
            [
                'id'                    => 8,
                'code'                  => 'vat_tax_id',
                'type'                  => 'text',
                'is_required'           => 0,
                'is_unique'             => 1,
            ],
            [
                'id'                    => 9,
                'code'                  => 'address',
                'type'                  => 'text',
                'is_required'           => 1,
                'value_per_locale'      => 1,
            ],
            [
                'id'                    => 10,
                'code'                  => 'city',
                'type'                  => 'text',
                'is_required'           => 1,
                'value_per_locale'      => 1,
            ],
            [
                'id'                    => 11,
                'code'                  => 'country',
                'type'                  => 'text',
                'is_required'           => 1,
                'value_per_locale'      => 1,
            ],
            [
                'id'                    => 12,
                'code'                  => 'state',
                'type'                  => 'text',
                'is_required'           => 1,
                'value_per_locale'      => 1,
            ],
            [
                'id'                    => 13,
                'code'                  => 'postcode',
                'type'                  => 'text',
                'is_required'           => 1,
            ],
            [
                'id'                    => 14,
                'code'                  => 'meta_title',
                'type'                  => 'text',
                'value_per_locale'      => 1,
            ],
            [
                'id'                    => 15,
                'code'                  => 'meta_keywords',
                'type'                  => 'text',
                'value_per_locale'      => 1,
            ],
            [
                'id'                    => 16,
                'code'                  => 'meta_description',
                'type'                  => 'textarea',
                'value_per_locale'      => 1,
            ],
            [
                'id'                    => 17,
                'code'                  => 'return_policy',
                'type'                  => 'textarea',
                'value_per_locale'      => 1,
                'enable_editor'         => 1,
            ],
            [
                'id'                    => 18,
                'code'                  => 'shipping_policy',
                'type'                  => 'textarea',
                'value_per_locale'      => 1,
                'enable_editor'         => 1,
            ],
            [
                'id'                    => 19,
                'code'                  => 'privacy_policy',
                'type'                  => 'textarea',
                'value_per_locale'      => 1,
                'enable_editor'         => 1,
            ],
            [
                'id'                    => 20,
                'code'                  => 'facebook',
                'type'                  => 'text',
            ],
            [
                'id'                    => 21,
                'code'                  => 'instagram',
                'type'                  => 'text',
            ],
            [
                'id'                    => 22,
                'code'                  => 'youtube',
                'type'                  => 'text',
            ],
            // [
            //     'id'                    => 23,
            //     'code'                  => 'commission_enable',
            //     'type'                  => 'boolean',
            //     'is_required'           => 1,
            // ],
            // [
            //     'id'                    => 24,
            //     'code'                  => 'commission_percentage',
            //     'type'                  => 'float',
            // ],
            // [
            //     'id'                    => 25,
            //     'code'                  => 'min_order_amount',
            //     'type'                  => 'price',
            //     'value_per_channel'     => 1,
            // ],
            // [
            //     'id'                    => 26,
            //     'code'                  => 'google_analytics_id',
            //     'type'                  => 'text',
            // ],
            // [
            //     'id'                    => 27,
            //     'code'                  => 'allowed_product_types',
            //     'type'                  => 'multiselect',
            //     'is_required'           => 1,
            // ],
        ];

        $attributes = collect($attributes)->map(function ($attribute) use ($parameters) {
            $translationKey = Str::replace('_', '-', $attribute['code']);

            return array_merge([
                'position'              => $attribute['id'],
                'validation'            => null,
                'is_required'           => 0,
                'is_unique'             => 0,
                'is_user_defined'       => 0,
                'is_visible_on_sign_up' => 0,
                'value_per_locale'      => 0,
                'value_per_channel'     => 0,
                'enable_editor'         => 0,
                'admin_name'            => trans(
                    key: "b2b_suite::app.seeders.company-attributes.$translationKey",
                    locale: app()->getLocale()
                ),
                'created_at'            => $parameters['now'],
                'updated_at'            => $parameters['now'],
            ], $attribute);
        })->toArray();

        DB::table('company_attributes')->insert($attributes);

        $translations = collect($attributes)->flatMap(function ($attribute) use ($parameters) {
            return collect($parameters['locales'])->map(function ($locale) use ($attribute) {
                $translationKey = Str::replace('_', '-', $attribute['code']);

                return [
                    'company_attribute_id'   => $attribute['id'],
                    'locale'                 => $locale,
                    'name'                   => trans(
                        key: "b2b_suite::app.seeders.company-attributes.$translationKey",
                        locale: $locale
                    ),
                ];
            });
        })->toArray();

        DB::table('company_attribute_translations')->insert($translations);
    }
}
