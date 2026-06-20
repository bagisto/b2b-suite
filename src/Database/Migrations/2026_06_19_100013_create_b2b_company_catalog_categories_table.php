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
        Schema::create('company_catalog_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_catalog_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->timestamps();

            $table->unique(['company_catalog_id', 'category_id'], 'company_catalog_categories_unique');
            $table->foreign('company_catalog_id')->references('id')->on('company_catalogs')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_catalog_categories');
    }
};
