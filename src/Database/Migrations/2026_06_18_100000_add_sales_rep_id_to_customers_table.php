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
            if (! Schema::hasColumn('customers', 'sales_rep_id')) {
                /**
                 * The admin user assigned as the sales representative / account manager
                 * for a company (type = company). Quotes and purchase orders raised for
                 * the company are owned by this rep.
                 */
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
            if (Schema::hasColumn('customers', 'sales_rep_id')) {
                $table->dropForeign(['sales_rep_id']);
                $table->dropColumn('sales_rep_id');
            }
        });
    }
};
