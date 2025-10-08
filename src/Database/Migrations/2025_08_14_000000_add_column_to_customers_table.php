<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('email');
            }

            if (! Schema::hasColumn('customers', 'type')) {
                $table->enum('type', ['user', 'company'])->default('company')->after('slug');
                $table->index(['type']);
            }

            if (! Schema::hasColumn('customers', 'company_role_id')) {
                $table->integer('company_role_id')->unsigned()->nullable()->after('customer_group_id');
                $table->foreign('company_role_id')->references('id')->on('company_roles')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['company_role_id']);
            $table->dropIndex(['type']);
            $table->dropColumn(['slug', 'type', 'is_approved', 'company_role_id']);
        });
    }
};
