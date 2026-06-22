<?php

namespace Webkul\B2BSuite\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreConfigTableSeeder extends Seeder
{
    /**
     * Enable the B2B Suite by default for every channel on install.
     *
     * @return void
     */
    public function run(array $parameters = [])
    {
        $now = $parameters['now'] ?? now()->toDateTimeString();

        foreach (DB::table('channels')->pluck('code') as $channelCode) {
            DB::table('core_config')->updateOrInsert(
                [
                    'code' => 'b2b.general.settings.active',
                    'channel_code' => $channelCode,
                    'locale_code' => null,
                ],
                [
                    'value' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
