<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->json('name');
            $table->string('slug')->unique();

            $table->json('short_description')->nullable();
            $table->json('description')->nullable();

            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable();

            $table->string('product_type')->default('physical');
            $table->string('status')->default('draft');

            $table->foreignId('brand_id')
                ->nullable()
                ->constrained('brands')
                ->nullOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            $table->foreignId('main_media_id')
                ->nullable()
                ->constrained('media_files')
                ->nullOnDelete();

            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->decimal('cost_price', 15, 2)->nullable();

            $table->boolean('track_stock')->default(true);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_quantity')->default(0);

            $table->boolean('requires_shipping')->default(true);
            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();

            $table->json('specifications')->nullable();
            $table->json('notes')->nullable();

            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['product_type', 'status']);
            $table->index(['is_active', 'sort_order']);
            $table->index(['brand_id', 'is_active']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};