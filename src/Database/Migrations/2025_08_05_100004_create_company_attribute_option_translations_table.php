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
        Schema::create('company_attribute_option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_attribute_option_id')
                ->constrained(indexName: 'company_attribute_option_id_foreign')
                ->cascadeOnDelete();
            $table->string('locale');
            $table->text('label')->nullable();

            $table->unique(
                ['company_attribute_option_id', 'locale'],
                'company_attr_option_locale_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_attribute_option_translations');
    }
};
