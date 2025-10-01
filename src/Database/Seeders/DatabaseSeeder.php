<?php

namespace Webkul\B2BSuite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * The seeders to be run.
     *
     * @var array
     */
    protected $seeders = [
        CompanyAttributeTableSeeder::class,
        CompanyAttributeOptionTableSeeder::class,
        CompanyAttributeGroupTableSeeder::class,
        CompanyAttributeGroupMappingTableSeeder::class,
        // CompanyRolesTableSeeder::class,
    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($this->seeders as $seeder) {
            $this->callWith($seeder, [
                'parameters' => [
                    'default_locale' => app()->getLocale(),
                    'locales'        => core()->getAllLocales()->pluck('code')->toArray(),
                    'now'            => now()->toDateTimeString(),
                ],
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
