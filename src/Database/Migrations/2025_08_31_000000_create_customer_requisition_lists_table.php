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
        Schema::create('customer_requisition_lists', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('company_id')->unsigned();
            $table->integer('customer_id')->unsigned();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('company_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            $table->index(['customer_id', 'status']);
            $table->index(['company_id', 'status']);
            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_requisition_lists');
    }
};
