<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('storefront_settings', 'products_categories_filter_title')) {
                $table->json('products_categories_filter_title')->nullable();
            }

            if (! Schema::hasColumn('storefront_settings', 'products_brands_filter_title')) {
                $table->json('products_brands_filter_title')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('storefront_settings', 'products_categories_filter_title') ? 'products_categories_filter_title' : null,
                Schema::hasColumn('storefront_settings', 'products_brands_filter_title') ? 'products_brands_filter_title' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
