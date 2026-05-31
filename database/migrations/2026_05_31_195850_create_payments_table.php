<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('payment_number')->unique();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            $table->string('payment_method')->default('cash');
            // cash, credit_card, bank_transfer, paypal, payplus, stripe

            $table->string('status')->default('pending');
            // pending, paid, failed, cancelled, refunded

            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('refunded_amount', 15, 2)->default(0);

            $table->string('transaction_id')->nullable();
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable();

            $table->json('provider_payload')->nullable();

            $table->dateTime('paid_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->dateTime('refunded_at')->nullable();

            $table->text('internal_notes')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['payment_method', 'status']);
            $table->index(['status', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};