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
            $table->string('discount_type')->nullable()->after('base_negotiated_total');
            $table->decimal('discount_value', 18, 4)->nullable()->after('discount_type');
        });

        Schema::table('customer_quote_items', function (Blueprint $table) {
            $table->string('discount_type')->nullable()->after('base_negotiated_total');
            $table->decimal('discount_value', 18, 4)->nullable()->after('discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_quotes', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });

        Schema::table('customer_quote_items', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value']);
        });
    }
};
