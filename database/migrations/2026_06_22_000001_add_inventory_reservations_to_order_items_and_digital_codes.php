<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('inventory_status')->nullable()->after('digital_code_id');
            $table->dateTime('inventory_reserved_at')->nullable()->after('inventory_status');
            $table->dateTime('inventory_fulfilled_at')->nullable()->after('inventory_reserved_at');
            $table->dateTime('inventory_released_at')->nullable()->after('inventory_fulfilled_at');

            $table->index(['inventory_status', 'inventory_reserved_at']);
        });

        Schema::table('product_digital_codes', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->after('reserved_at')
                ->constrained('orders')
                ->nullOnDelete();
            $table->foreignId('order_item_id')
                ->nullable()
                ->after('order_id')
                ->constrained('order_items')
                ->nullOnDelete();

            $table->index(['order_id', 'status']);
            $table->index(['order_item_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('product_digital_codes', function (Blueprint $table) {
            $table->dropIndex(['order_item_id', 'status']);
            $table->dropIndex(['order_id', 'status']);
            $table->dropConstrainedForeignId('order_item_id');
            $table->dropConstrainedForeignId('order_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['inventory_status', 'inventory_reserved_at']);
            $table->dropColumn([
                'inventory_status',
                'inventory_reserved_at',
                'inventory_fulfilled_at',
                'inventory_released_at',
            ]);
        });
    }
};
