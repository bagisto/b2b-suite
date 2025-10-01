<?php

namespace Webkul\B2BSuite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyAttributeGroupMappingTableSeeder extends Seeder
{
    public function run(array $parameters = [])
    {
        DB::table('company_attribute_group_mappings')->truncate();

        DB::table('company_attribute_group_mappings')->insert([
            /**
             * General Group Attributes
             */
            [
                'company_attribute_id'        => 1,
                'company_attribute_group_id'  => 1,
                'position'                    => 1,
            ], [
                'company_attribute_id'        => 2,
                'company_attribute_group_id'  => 1,
                'position'                    => 2,
            ], [
                'company_attribute_id'        => 3,
                'company_attribute_group_id'  => 1,
                'position'                    => 3,
            ], [
                'company_attribute_id'        => 4,
                'company_attribute_group_id'  => 1,
                'position'                    => 4,
            ], [
                'company_attribute_id'        => 5,
                'company_attribute_group_id'  => 1,
                'position'                    => 5,
            ], [
                'company_attribute_id'        => 6,
                'company_attribute_group_id'  => 1,
                'position'                    => 6,
            ], [
                'company_attribute_id'        => 7,
                'company_attribute_group_id'  => 1,
                'position'                    => 7,
            ], [
                'company_attribute_id'        => 8,
                'company_attribute_group_id'  => 1,
                'position'                    => 8,
            ],

            /**
             * Address Group Attributes
             */
            [
                'company_attribute_id'        => 9,
                'company_attribute_group_id'  => 2,
                'position'                    => 1,
            ], [
                'company_attribute_id'        => 10,
                'company_attribute_group_id'  => 2,
                'position'                    => 2,
            ], [
                'company_attribute_id'        => 11,
                'company_attribute_group_id'  => 2,
                'position'                    => 3,
            ], [
                'company_attribute_id'        => 12,
                'company_attribute_group_id'  => 2,
                'position'                    => 4,
            ], [
                'company_attribute_id'        => 13,
                'company_attribute_group_id'  => 2,
                'position'                    => 5,
            ],

            /**
             * Meta Description Group Attributes
             */
            [
                'company_attribute_id'        => 14,
                'company_attribute_group_id'  => 3,
                'position'                    => 1,
            ], [
                'company_attribute_id'        => 15,
                'company_attribute_group_id'  => 3,
                'position'                    => 2,
            ], [
                'company_attribute_id'        => 16,
                'company_attribute_group_id'  => 3,
                'position'                    => 3,
            ],

            /**
             * Policy Group Attributes
             */
            [
                'company_attribute_id'        => 17,
                'company_attribute_group_id'  => 4,
                'position'                    => 1,
            ], [
                'company_attribute_id'        => 18,
                'company_attribute_group_id'  => 4,
                'position'                    => 2,
            ], [
                'company_attribute_id'        => 19,
                'company_attribute_group_id'  => 4,
                'position'                    => 3,
            ],

            /**
             * Social Links Group Attributes
             */
            [
                'company_attribute_id'        => 20,
                'company_attribute_group_id'  => 5,
                'position'                    => 1,
            ], [
                'company_attribute_id'        => 21,
                'company_attribute_group_id'  => 5,
                'position'                    => 2,
            ], [
                'company_attribute_id'        => 22,
                'company_attribute_group_id'  => 5,
                'position'                    => 3,
            ],

            // /**
            //  * Settings Group Attributes
            //  */
            // [
            //     'company_attribute_id'        => 23,
            //     'company_attribute_group_id'  => 6,
            //     'position'                    => 1,
            // ],
            // [
            //     'company_attribute_id'        => 24,
            //     'company_attribute_group_id'  => 6,
            //     'position'                    => 2,
            // ], [
            //     'company_attribute_id'        => 25,
            //     'company_attribute_group_id'  => 6,
            //     'position'                    => 3,
            // ], [
            //     'company_attribute_id'        => 26,
            //     'company_attribute_group_id'  => 6,
            //     'position'                    => 4,
            // ], [
            //     'company_attribute_id'        => 27,
            //     'company_attribute_group_id'  => 6,
            //     'position'                    => 5,
            // ], [
            //     'company_attribute_id'        => 28,
            //     'company_attribute_group_id'  => 6,
            //     'position'                    => 6,
            // ],
        ]);
    }
}
