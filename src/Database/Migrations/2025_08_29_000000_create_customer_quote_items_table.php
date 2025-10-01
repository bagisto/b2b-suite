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
        Schema::create('customer_quote_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_quote_id')->unsigned();
            $table->integer('product_id')->unsigned()->nullable();
            $table->string('type');
            $table->string('sku');
            $table->string('name');
            $table->integer('qty')->default(1);

            $table->decimal('price', 18, 4)->default(0);
            $table->decimal('base_price', 18, 4)->default(0);

            $table->decimal('total', 18, 4)->default(0);
            $table->decimal('base_total', 18, 4)->default(0);

            $table->integer('negotiated_qty')->default(1);

            $table->decimal('negotiated_price', 18, 4)->default(0);
            $table->decimal('base_negotiated_price', 18, 4)->default(0);

            $table->decimal('negotiated_total', 18, 4)->default(0);
            $table->decimal('base_negotiated_total', 18, 4)->default(0);

            $table->text('note')->nullable();

            $table->enum('status', ['draft', 'open', 'negotiation', 'accepted', 'ordered', 'expired', 'rejected', 'completed'])->default('draft');
            $table->json('additional')->nullable();
            $table->timestamps();

            $table->foreign('customer_quote_id')->references('id')->on('customer_quotes')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_quote_items');
    }
};
