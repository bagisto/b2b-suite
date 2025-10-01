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
        // Create the customer_flat table with same structure
        Schema::create('customer_flat', function (Blueprint $table) {
            $table->increments('id');

            // Foreign key to customers table
            $table->integer('customer_id')->unsigned();

            // Multi-locale and multi-channel support
            $table->string('locale')->nullable();
            $table->string('channel')->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('slug')->nullable();
            $table->string('business_name')->nullable();
            $table->string('website_url')->nullable();
            $table->string('vat_tax_id')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();

            // SEO fields
            $table->text('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            // Policy fields
            $table->text('return_policy')->nullable();
            $table->text('shipping_policy')->nullable();
            $table->text('privacy_policy')->nullable();

            // Social media links
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('twitter')->nullable();

            $table->timestamps();

            // Unique index for customer_id, channel, locale combination
            $table->unique(['customer_id', 'channel', 'locale'], 'customer_flat_unique_index');

            // Foreign key constraints
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            // Additional indexes for performance
            $table->index(['locale']);
            $table->index(['channel']);
            $table->index(['customer_id', 'locale']);
            $table->index(['customer_id', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the customer_flat table
        Schema::dropIfExists('customer_flat');

        // Recreate the company_flat table
        Schema::create('company_flat', function (Blueprint $table) {
            $table->increments('id');

            // Foreign key to companies table
            $table->integer('company_id')->unsigned();

            // Multi-locale and multi-channel support
            $table->string('locale')->nullable();
            $table->string('channel')->nullable();

            // Company attributes from CompanyAttributeTableSeeder
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('slug')->nullable();
            $table->string('business_name')->nullable();
            $table->string('vat_tax_id')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode')->nullable();

            // SEO fields
            $table->text('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            // Policy fields
            $table->text('return_policy')->nullable();
            $table->text('shipping_policy')->nullable();
            $table->text('privacy_policy')->nullable();

            // Social media links
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('youtube')->nullable();
            $table->string('twitter')->nullable();

            $table->timestamps();

            // Unique index for company_id, channel, locale combination
            $table->unique(['company_id', 'channel', 'locale'], 'company_flat_unique_index');

            // Foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }
};
