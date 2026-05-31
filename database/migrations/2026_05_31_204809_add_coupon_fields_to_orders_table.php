<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')
                    ->nullable()
                    ->after('shipping_method_id')
                    ->constrained('coupons')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code')->nullable()->after('coupon_id');
            }

            if (! Schema::hasColumn('orders', 'coupon_discount_type')) {
                $table->string('coupon_discount_type')->nullable()->after('coupon_code');
            }

            if (! Schema::hasColumn('orders', 'coupon_discount_value')) {
                $table->decimal('coupon_discount_value', 15, 2)->nullable()->after('coupon_discount_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'coupon_id')) {
                $table->dropConstrainedForeignId('coupon_id');
            }

            if (Schema::hasColumn('orders', 'coupon_code')) {
                $table->dropColumn('coupon_code');
            }

            if (Schema::hasColumn('orders', 'coupon_discount_type')) {
                $table->dropColumn('coupon_discount_type');
            }

            if (Schema::hasColumn('orders', 'coupon_discount_value')) {
                $table->dropColumn('coupon_discount_value');
            }
        });
    }
};