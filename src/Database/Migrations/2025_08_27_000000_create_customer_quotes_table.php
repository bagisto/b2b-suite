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
        Schema::create('customer_quotes', function (Blueprint $table) {
            $table->increments('id');

            $table->string('quotation_number')->nullable();
            $table->string('po_number')->nullable();
            $table->string('shipping_number')->nullable();

            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('company_id')->unsigned();
            $table->integer('customer_id')->unsigned();
            $table->integer('agent_id')->unsigned()->nullable();

            $table->decimal('total', 18, 4)->default(0);
            $table->decimal('base_total', 18, 4)->default(0);

            $table->decimal('negotiated_total', 18, 4)->default(0);
            $table->decimal('base_negotiated_total', 18, 4)->default(0);

            $table->date('order_date')->nullable();
            $table->date('expected_arrival_date')->nullable();

            $table->date('expiration_date')->nullable();
            $table->boolean('soft_deleted')->default(0);

            $table->enum('state', ['quotation', 'purchase_order'])->default('quotation');
            $table->enum('status', ['draft', 'open', 'negotiation', 'accepted', 'ordered', 'expired', 'rejected', 'completed'])->default('draft');

            $table->integer('order_id')->unsigned()->nullable();

            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('admins')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_quotes');
    }
};
