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
        Schema::table('customer_quote_quotations', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_quote_quotations', 'discount_type')) {
                /**
                 * The per-item discount captured in this offer snapshot, so the negotiation
                 * message log can show the item discount separately from the whole-quote
                 * discount (mirroring the quote items table). Null = no item discount.
                 */
                $table->string('discount_type')->nullable()->after('qty');
                $table->decimal('discount_value', 12, 4)->nullable()->after('discount_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_quote_quotations', function (Blueprint $table) {
            if (Schema::hasColumn('customer_quote_quotations', 'discount_type')) {
                $table->dropColumn(['discount_type', 'discount_value']);
            }
        });
    }
};
