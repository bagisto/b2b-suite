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
        Schema::create('customer_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->string('locale')->nullable();
            $table->string('channel')->nullable();
            $table->text('text_value')->nullable();
            $table->boolean('boolean_value')->nullable();
            $table->integer('integer_value')->nullable();
            $table->decimal('float_value', 12, 4)->nullable();
            $table->dateTime('datetime_value')->nullable();
            $table->date('date_value')->nullable();
            $table->json('json_value')->nullable();
            $table->unsignedInteger('customer_id');
            $table->foreignId('company_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('unique_id')->nullable();

            $table->unique([
                'channel',
                'locale',
                'company_attribute_id',
                'customer_id',
            ], 'channel_locale_attribute_value_index_unique');

            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_attribute_values');
    }
};
