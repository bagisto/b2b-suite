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
            if (! Schema::hasColumn('customers', 'company_catalog_id')) {
                /**
                 * Holds the company catalog assigned to a company (type = company).
                 * Members inherit the catalog through their customer group.
                 */
                $table->integer('company_catalog_id')->unsigned()->nullable()->after('company_role_id');

                $table->foreign('company_catalog_id')->references('id')->on('company_catalogs')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'company_catalog_id')) {
                $table->dropForeign(['company_catalog_id']);
                $table->dropColumn('company_catalog_id');
            }
        });
    }
};
