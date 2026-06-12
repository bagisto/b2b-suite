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
        Schema::create('company_attribute_group_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_attribute_group_id')
                ->constrained(indexName: 'company_attribute_group_id_foreign')
                ->cascadeOnDelete();
            $table->string('locale');
            $table->text('name')->nullable();

            $table->unique(
                ['company_attribute_group_id', 'locale'],
                'company_attr_group_local_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_attribute_group_translations');
    }
};
