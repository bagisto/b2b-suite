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
        Schema::table('company_catalogs', function (Blueprint $table) {
            if (! Schema::hasColumn('company_catalogs', 'created_by')) {
                /**
                 * The admin who created the catalog. Only the creator (or a super-admin)
                 * may edit/delete it; other admins who can see it get a read-only view.
                 * Null = created before this column existed (treated as super-admin owned).
                 */
                $table->integer('created_by')->unsigned()->nullable()->after('customer_group_id');

                $table->foreign('created_by')->references('id')->on('admins')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_catalogs', function (Blueprint $table) {
            if (Schema::hasColumn('company_catalogs', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
