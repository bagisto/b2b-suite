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
            if (! Schema::hasColumn('customers', 'type')) {
                $table->enum('type', ['user', 'company'])->default('user')->after('email');

                $table->index(['type']);
            }

            if (! Schema::hasColumn('customers', 'company_role_id')) {
                $table->integer('company_role_id')->unsigned()->nullable()->after('customer_group_id');

                $table->foreign('company_role_id')->references('id')->on('company_roles')->onDelete('set null');
            }

            if (! Schema::hasColumn('customers', 'company_catalog_id')) {
                $table->integer('company_catalog_id')->unsigned()->nullable()->after('company_role_id');

                $table->foreign('company_catalog_id')->references('id')->on('company_catalogs')->onDelete('set null');
            }

            if (! Schema::hasColumn('customers', 'sales_rep_id')) {
                $table->integer('sales_rep_id')->unsigned()->nullable()->after('company_catalog_id');

                $table->foreign('sales_rep_id')->references('id')->on('admins')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['sales_rep_id']);
            $table->dropForeign(['company_catalog_id']);
            $table->dropForeign(['company_role_id']);
            $table->dropIndex(['type']);
            $table->dropColumn(['type', 'company_role_id', 'company_catalog_id', 'sales_rep_id']);
        });
    }
};
