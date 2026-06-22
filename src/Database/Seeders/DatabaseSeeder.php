<?php

namespace Webkul\B2BSuite\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * The seeders to be run, ordered so that parent tables are populated before
     * their dependents. Each seeder cleans its own tables child-first (relying on
     * `ON DELETE CASCADE`) before inserting, so no foreign-key check toggling is needed.
     *
     * @var array
     */
    protected $seeders = [
        CoreConfigTableSeeder::class,
        CompanyAttributeTableSeeder::class,
        CompanyAttributeOptionTableSeeder::class,
        CompanyAttributeGroupTableSeeder::class,
        CompanyAttributeGroupMappingTableSeeder::class,
    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->seeders as $seeder) {
            $this->callWith($seeder, [
                'parameters' => [
                    'default_locale' => app()->getLocale(),
                    'locales' => core()->getAllLocales()->pluck('code')->toArray(),
                    'now' => now()->toDateTimeString(),
                ],
            ]);
        }
    }
}
