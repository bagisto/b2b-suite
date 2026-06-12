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
        Schema::create('company_attribute_group_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_attribute_id')
                ->constrained(indexName: 'company_attr_id_foreign')
                ->onDelete('cascade');
            $table->foreignId('company_attribute_group_id')
                ->constrained(indexName: 'company_attr_group_id_foreign')
                ->cascadeOnDelete();
            $table->integer('position')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_attribute_group_mappings');
    }
};
