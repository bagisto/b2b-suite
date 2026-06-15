<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Earlier installs created `customers.type` with a default of `company`, which
     * mislabelled regular customers (registered through the core flow, which never sets
     * `type`) as company accounts and hid their account menu. Regular customers should
     * default to `user`; only real company accounts/users (which always carry a
     * `company_role_id`) stay `company`.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('type', ['user', 'company'])->default('user')->change();
        });

        DB::table('customers')
            ->whereNull('company_role_id')
            ->update(['type' => 'user']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('type', ['user', 'company'])->default('company')->change();
        });
    }
};
