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
        Schema::create('company_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('admin_name');
            $table->string('type');
            $table->string('validation')->nullable();
            $table->string('regex')->nullable();
            $table->integer('position')->nullable();
            $table->boolean('is_required')->default(0);
            $table->boolean('is_unique')->default(0);
            $table->boolean('is_user_defined')->default(1);
            $table->boolean('is_visible_on_sign_up')->default(0);
            $table->boolean('default_value')->nullable();
            $table->boolean('value_per_locale')->default(0);
            $table->boolean('value_per_channel')->default(0);
            $table->boolean('enable_editor')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_attributes');
    }
};
