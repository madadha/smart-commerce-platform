<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_digital_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('product_variant_id')
                ->nullable()
                ->constrained('product_variants')
                ->nullOnDelete();

            $table->string('code')->unique();

            $table->string('status')->default('available');
            // available, reserved, sold, cancelled, expired

            $table->string('source')->nullable();
            // supplier, manual, import, api

            $table->dateTime('expires_at')->nullable();

            $table->foreignId('reserved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('reserved_at')->nullable();

            $table->foreignId('sold_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('sold_at')->nullable();

            $table->text('internal_notes')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index(['product_variant_id', 'status']);
            $table->index(['status', 'is_active']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_digital_codes');
    }
};