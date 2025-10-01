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
        Schema::create('company_attribute_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_attribute_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('admin_name')->nullable();
            $table->integer('sort_order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_attribute_options');
    }
};
