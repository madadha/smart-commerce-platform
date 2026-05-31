<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            $table->string('cart_number')->unique();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            $table->foreignId('shipping_method_id')
                ->nullable()
                ->constrained('shipping_methods')
                ->nullOnDelete();

            $table->foreignId('coupon_id')
                ->nullable()
                ->constrained('coupons')
                ->nullOnDelete();

            $table->string('coupon_code')->nullable();
            $table->string('coupon_discount_type')->nullable();
            $table->decimal('coupon_discount_value', 15, 2)->nullable();

            $table->string('status')->default('active');
            // active, converted, abandoned, cancelled

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('shipping_total', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->dateTime('converted_at')->nullable();
            $table->dateTime('abandoned_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['status', 'is_active']);
            $table->index(['user_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['coupon_id', 'status']);
            $table->index(['shipping_method_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};