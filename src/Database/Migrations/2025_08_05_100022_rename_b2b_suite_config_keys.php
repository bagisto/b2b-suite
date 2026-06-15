<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The B2B Suite config namespace was renamed from `b2b_suite` to `b2b`. Existing
     * stored config values in `core_config` use the old `b2b_suite.*` codes, so rename
     * them to keep saved settings (active flag, quote settings, etc.) intact.
     */
    public function up(): void
    {
        DB::table('core_config')
            ->where('code', 'like', 'b2b_suite.%')
            ->update(['code' => DB::raw("REPLACE(code, 'b2b_suite.', 'b2b.')")]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('core_config')
            ->where('code', 'like', 'b2b.%')
            ->update(['code' => DB::raw("REPLACE(code, 'b2b.', 'b2b_suite.')")]);
    }
};
