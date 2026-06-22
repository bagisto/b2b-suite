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
        Schema::create('b2b_company_attribute_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_attribute_id')
                ->constrained(table: 'b2b_company_attributes', indexName: 'b2b_comp_attr_trans_attr_fk')
                ->cascadeOnDelete();
            $table->string('locale');
            $table->text('name')->nullable();

            $table->unique(
                ['company_attribute_id', 'locale'],
                'company_attr_local_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('b2b_company_attribute_translations');
    }
};
