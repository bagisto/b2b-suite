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
        Schema::create('b2b_company_credits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->string('credit_currency_code')->nullable();
            $table->decimal('credit_limit', 18, 4)->default(0);
            $table->decimal('outstanding_balance', 18, 4)->default(0);
            $table->boolean('allow_exceed_limit')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique('company_id');
            $table->foreign('company_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::create('b2b_company_credit_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_credit_id')->unsigned();
            $table->enum('operation', ['allocated', 'updated', 'purchased', 'reimbursed', 'refunded', 'reverted']);
            $table->decimal('amount', 18, 4)->default(0);
            $table->decimal('outstanding_balance_after', 18, 4)->default(0);
            $table->decimal('available_credit_after', 18, 4)->default(0);
            $table->decimal('credit_limit_after', 18, 4)->default(0);
            $table->integer('order_id')->unsigned()->nullable();
            $table->string('reference')->nullable();
            $table->text('comment')->nullable();
            $table->string('actor_type')->nullable();
            $table->integer('actor_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('company_credit_id')->references('id')->on('b2b_company_credits')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_company_credit_transactions');
        Schema::dropIfExists('b2b_company_credits');
    }
};
