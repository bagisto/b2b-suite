<?php

namespace Webkul\B2BSuite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CompanyAttributeOptionTableSeeder extends Seeder
{
    public function run(array $parameters = [])
    {
        DB::table('company_attribute_options')->truncate();

        DB::table('company_attribute_option_translations')->truncate();

        // $attributeOptions = collect(config('product_types'))
        //     ->values()
        //     ->map(function ($type, $index) {
        //         return [
        //             'id'                     => $index + 1,
        //             'name'                   => $type['name'],
        //             'admin_name'             => trans(
        //                 key: $type['name'],
        //                 locale: app()->getLocale()
        //             ),
        //             'sort_order'             => $index + 1,
        //             'company_attribute_id'   => 22,
        //         ];
        //     })
        //     ->toArray();
        $attributeOptions = [];

        DB::table('company_attribute_options')->insert(
            collect($attributeOptions)->map(function ($option) {
                return Arr::except($option, ['name']);
            })->toArray()
        );

        $translations = collect($attributeOptions)->flatMap(function ($option) use ($parameters) {
            return collect($parameters['locales'])->map(function ($locale) use ($option) {
                return [
                    'company_attribute_option_id' => $option['id'],
                    'label'                       => trans(
                        key: $option['name'],
                        locale: $locale
                    ),
                    'locale'                      => $locale,
                ];
            });
        })->toArray();

        DB::table('company_attribute_option_translations')->insert($translations);
    }
}
