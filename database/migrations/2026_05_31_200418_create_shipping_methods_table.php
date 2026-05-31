<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();

            $table->json('name');
            $table->string('slug')->unique();

            $table->json('description')->nullable();

            $table->string('type')->default('home_delivery');
            // home_delivery, pickup, express, standard, free, external_company

            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            $table->decimal('base_cost', 15, 2)->default(0);
            $table->decimal('free_shipping_min_total', 15, 2)->nullable();

            $table->unsignedInteger('min_delivery_days')->nullable();
            $table->unsignedInteger('max_delivery_days')->nullable();

            $table->string('external_company_name')->nullable();
            $table->string('external_company_phone')->nullable();
            $table->string('external_company_website')->nullable();

            $table->json('allowed_cities')->nullable();
            $table->json('excluded_cities')->nullable();

            $table->boolean('requires_address')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['country_id', 'is_active']);
            $table->index(['is_default', 'is_active']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};