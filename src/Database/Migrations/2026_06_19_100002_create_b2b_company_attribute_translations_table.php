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
        Schema::create('company_attribute_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_attribute_id')
                ->constrained()
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
        Schema::dropIfExists('company_attribute_translations');
    }
};
