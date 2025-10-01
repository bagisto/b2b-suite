<?php

namespace Webkul\B2BSuite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyAttributeGroupTableSeeder extends Seeder
{
    public function run(array $parameters = [])
    {
        DB::table('company_attribute_groups')->truncate();

        DB::table('company_attribute_group_translations')->truncate();

        $attributeGroups = [
            [
                'id'              => 1,
                'code'            => 'general',
                'admin_name'      => trans(
                    key: 'b2b_suite::app.seeders.company-attribute-groups.general',
                    locale: $parameters['default_locale']
                ),
                'column'          => 1,
                'is_user_defined' => 0,
                'position'        => 1,
            ], [
                'id'              => 2,
                'code'            => 'address',
                'admin_name'      => trans(
                    key: 'b2b_suite::app.seeders.company-attribute-groups.address',
                    locale: $parameters['default_locale']
                ),
                'column'          => 1,
                'is_user_defined' => 0,
                'position'        => 2,
            ], [
                'id'              => 3,
                'code'            => 'meta_description',
                'admin_name'      => trans(
                    key: 'b2b_suite::app.seeders.company-attribute-groups.meta-description',
                    locale: $parameters['default_locale']
                ),
                'column'          => 2,
                'is_user_defined' => 0,
                'position'        => 1,
            ], [
                'id'              => 4,
                'code'            => 'policies',
                'admin_name'      => trans(
                    key: 'b2b_suite::app.seeders.company-attribute-groups.policies',
                    locale: $parameters['default_locale']
                ),
                'column'          => 1,
                'is_user_defined' => 0,
                'position'        => 3,
            ], [
                'id'              => 5,
                'code'            => 'social_links',
                'admin_name'      => trans(
                    key: 'b2b_suite::app.seeders.company-attribute-groups.social-links',
                    locale: $parameters['default_locale']
                ),
                'column'          => 2,
                'is_user_defined' => 0,
                'position'        => 2,
                // ], [
                //     'id'              => 6,
                //     'code'            => 'settings',
                //     'admin_name'      => trans(
                //         key: 'b2b_suite::app.seeders.company-attribute-groups.settings',
                //         locale: $parameters['default_locale']
                //     ),
                //     'column'          => 2,
                //     'is_user_defined' => 0,
                //     'position'        => 3,
            ],
        ];

        DB::table('company_attribute_groups')->insert($attributeGroups);

        $translations = collect($attributeGroups)->flatMap(function ($group) use ($parameters) {
            return collect($parameters['locales'])->map(function ($locale) use ($group) {
                $translationKey = Str::replace('_', '-', $group['code']);

                return [
                    'company_attribute_group_id' => $group['id'],
                    'locale'                     => $locale,
                    'name'                       => trans(
                        key: "b2b_suite::app.seeders.company-attribute-groups.$translationKey",
                        locale: $locale
                    ),
                ];
            });
        })->toArray();

        DB::table('company_attribute_group_translations')->insert($translations);
    }
}
