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
        Schema::create('company_attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('admin_name');
            $table->integer('column')->default(1);
            $table->integer('position');
            $table->boolean('is_user_defined')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_attribute_groups');
    }
};
