<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            if (! Schema::hasColumn('product_variants', 'package_amount')) {
                $table->decimal('package_amount', 12, 2)->nullable()->after('option_values');
            }

            if (! Schema::hasColumn('product_variants', 'package_unit')) {
                $table->string('package_unit')->nullable()->after('package_amount');
            }

            if (! Schema::hasColumn('product_variants', 'package_label')) {
                $table->json('package_label')->nullable()->after('package_unit');
            }

            if (! Schema::hasColumn('product_variants', 'provider_package_id')) {
                $table->string('provider_package_id')->nullable()->after('provider_sku');
            }

            if (! Schema::hasColumn('product_variants', 'provider_payload')) {
                $table->json('provider_payload')->nullable()->after('provider_package_id');
            }

            if (! Schema::hasColumn('product_variants', 'fulfillment_mode')) {
                $table->string('fulfillment_mode')->nullable()->after('provider_payload');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            foreach ([
                'package_amount',
                'package_unit',
                'package_label',
                'provider_package_id',
                'provider_payload',
                'fulfillment_mode',
            ] as $column) {
                if (Schema::hasColumn('product_variants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
