<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();

            $table->json('name');
            $table->json('description')->nullable();

            $table->string('discount_type')->default('percentage');
            // percentage, fixed_amount, free_shipping

            $table->decimal('discount_value', 15, 2)->default(0);

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            $table->decimal('minimum_order_total', 15, 2)->nullable();
            $table->decimal('maximum_discount_amount', 15, 2)->nullable();

            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->unsignedInteger('used_count')->default(0);

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['discount_type', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};