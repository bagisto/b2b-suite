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
        Schema::create('customer_quote_quotations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('message_id')->unsigned();
            $table->integer('quote_id')->unsigned();
            $table->integer('quote_item_id')->unsigned();

            $table->string('sku');
            $table->string('name');
            $table->integer('qty')->default(1);

            $table->decimal('price', 18, 4)->default(0);
            $table->decimal('base_price', 18, 4)->default(0);

            $table->decimal('total', 18, 4)->default(0);
            $table->decimal('base_total', 18, 4)->default(0);

            $table->boolean('is_accepted')->default(false);
            $table->string('accepted_by'); // 'customer' or 'admin'

            $table->timestamps();

            $table->foreign('quote_id')->references('id')->on('customer_quotes')->onDelete('cascade');
            $table->foreign('quote_item_id')->references('id')->on('customer_quote_items')->onDelete('cascade');
            $table->foreign('message_id')->references('id')->on('customer_quote_messages')->onDelete('cascade');
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
