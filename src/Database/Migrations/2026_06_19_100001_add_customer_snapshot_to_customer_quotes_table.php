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
        Schema::table('customer_quotes', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_quotes', 'customer_name')) {
                // Denormalised snapshot of the creating member — kept for display once the
                // member is removed from the company (customer_id is then nulled).
                $table->string('customer_name')->nullable()->after('customer_id');
            }

            if (! Schema::hasColumn('customer_quotes', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_name');
            }
        });

        // Allow customer_id to be cleared and have a customer deletion null it (instead of
        // cascade-deleting the company's quote).
        Schema::table('customer_quotes', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('customer_quotes', function (Blueprint $table) {
            $table->integer('customer_id')->unsigned()->nullable()->change();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_quotes', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('customer_quotes', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            if (Schema::hasColumn('customer_quotes', 'customer_name')) {
                $table->dropColumn(['customer_name', 'customer_email']);
            }
        });
    }
};
