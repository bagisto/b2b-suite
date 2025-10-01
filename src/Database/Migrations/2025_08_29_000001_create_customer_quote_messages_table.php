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
        Schema::create('customer_quote_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quote_id')->unsigned();
            $table->integer('user_id')->unsigned(); // can be customer or admin
            $table->string('user_type'); // 'customer' or 'admin'
            $table->text('message');
            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('quote_id')->references('id')->on('customer_quotes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_quote_messages');
    }
};
