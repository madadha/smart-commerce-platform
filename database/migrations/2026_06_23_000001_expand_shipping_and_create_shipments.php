<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->decimal('per_kg_cost', 15, 2)->default(0)->after('base_cost');
            $table->decimal('min_order_total', 15, 2)->nullable()->after('per_kg_cost');
            $table->decimal('max_order_total', 15, 2)->nullable()->after('min_order_total');
            $table->decimal('min_weight', 10, 3)->nullable()->after('max_order_total');
            $table->decimal('max_weight', 10, 3)->nullable()->after('min_weight');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_country_id')->nullable()->after('shipping_method_id')
                ->constrained('countries')->nullOnDelete();
            $table->decimal('shipping_weight', 10, 3)->default(0)->after('shipping_total');
            $table->unsignedInteger('shipping_min_delivery_days')->nullable()->after('shipping_weight');
            $table->unsignedInteger('shipping_max_delivery_days')->nullable()->after('shipping_min_delivery_days');
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            $table->string('carrier_name')->nullable();
            $table->string('carrier_service')->nullable();
            $table->string('tracking_number')->nullable()->index();
            $table->string('tracking_url')->nullable();
            $table->string('label_path')->nullable();
            $table->json('shipping_address')->nullable();
            $table->decimal('weight', 10, 3)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->dateTime('estimated_delivery_at')->nullable();
            $table->dateTime('shipped_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status']);
            $table->index(['status', 'estimated_delivery_at']);
        });

        Schema::create('shipment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('occurred_at');
            $table->boolean('is_customer_visible')->default(true);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['shipment_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_events');
        Schema::dropIfExists('shipments');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_country_id');
            $table->dropColumn(['shipping_weight', 'shipping_min_delivery_days', 'shipping_max_delivery_days']);
        });

        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->dropColumn(['per_kg_cost', 'min_order_total', 'max_order_total', 'min_weight', 'max_weight']);
        });
    }
};
