<?php

namespace Webkul\B2BSuite\Database\Seeders\DemoSeeders;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Base for the demo-data seeders.
 *
 * Every demo customer (company owners and their members) is created with an email on the
 * {@see self::DEMO_DOMAIN} marker domain, so the whole demo data set can be located and wiped
 * in isolation without touching real records. All inserts go through {@see self::bulkInsert()}
 * in bounded chunks, so the seeders scale to tens of thousands of companies without exhausting
 * memory or the DB packet size.
 */
abstract class DemoSeeder extends Seeder
{
    /**
     * Marker domain shared by every demo customer (company + member) email.
     */
    public const DEMO_DOMAIN = 'bagisto-b2b-demo.test';

    /**
     * Rows inserted per statement — keeps memory and the MySQL packet bounded at scale.
     */
    protected const INSERT_CHUNK = 500;

    /**
     * Companies processed per batch when building dependent rows (ids are read back per batch).
     */
    protected const COMPANY_BATCH = 250;

    /**
     * Shared faker generator (US locale for realistic company / address data).
     */
    protected ?Generator $faker = null;

    /**
     * Lazily resolve the shared faker generator.
     */
    protected function faker(): Generator
    {
        return $this->faker ??= FakerFactory::create('en_US');
    }

    /**
     * Insert rows in bounded chunks so large data sets stay within memory / packet limits.
     */
    protected function bulkInsert(string $table, array $rows): void
    {
        foreach (array_chunk($rows, self::INSERT_CHUNK) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    /**
     * Run a callback with the connection's query-event dispatcher detached.
     *
     * Dev query collectors (e.g. Debugbar) interpolate `?` placeholders into the SQL for every
     * executed query; a bulk insert whose values themselves contain `?` (quote message bodies)
     * makes that interpolation explode into a quadratic slow-down. Seeding fires no listeners we
     * need, so detach them for the duration — turning a multi-minute run into seconds.
     */
    protected function withoutQueryEvents(callable $callback)
    {
        $connection = DB::connection();

        $dispatcher = $connection->getEventDispatcher();

        $connection->unsetEventDispatcher();

        try {
            return $callback();
        } finally {
            if ($dispatcher) {
                $connection->setEventDispatcher($dispatcher);
            }
        }
    }

    /**
     * Ids of every demo company customer (type = company), oldest first.
     */
    protected function demoCompanyIds(): Collection
    {
        return DB::table('customers')
            ->where('type', 'company')
            ->where('email', 'like', '%@'.self::DEMO_DOMAIN)
            ->orderBy('id')
            ->pluck('id');
    }
}
