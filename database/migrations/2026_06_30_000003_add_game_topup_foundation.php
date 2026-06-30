<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('storefront_settings', 'enable_game_topups')) {
                $table->boolean('enable_game_topups')->default(true)->after('show_cookie_consent');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'game_title')) {
                $table->json('game_title')->nullable()->after('youtube_enabled');
            }

            if (! Schema::hasColumn('products', 'game_currency_name')) {
                $table->json('game_currency_name')->nullable()->after('game_title');
            }

            if (! Schema::hasColumn('products', 'game_delivery_mode')) {
                $table->string('game_delivery_mode')->default('manual')->after('game_currency_name');
            }

            if (! Schema::hasColumn('products', 'game_provider')) {
                $table->string('game_provider')->nullable()->after('game_delivery_mode');
            }

            if (! Schema::hasColumn('products', 'game_provider_sku')) {
                $table->string('game_provider_sku')->nullable()->after('game_provider');
            }

            if (! Schema::hasColumn('products', 'game_requires_player_id')) {
                $table->boolean('game_requires_player_id')->default(true)->after('game_provider_sku');
            }

            if (! Schema::hasColumn('products', 'game_requires_region')) {
                $table->boolean('game_requires_region')->default(false)->after('game_requires_player_id');
            }

            if (! Schema::hasColumn('products', 'game_requires_server')) {
                $table->boolean('game_requires_server')->default(false)->after('game_requires_region');
            }

            if (! Schema::hasColumn('products', 'game_can_validate_player')) {
                $table->boolean('game_can_validate_player')->default(false)->after('game_requires_server');
            }

            if (! Schema::hasColumn('products', 'game_player_id_label')) {
                $table->json('game_player_id_label')->nullable()->after('game_can_validate_player');
            }

            if (! Schema::hasColumn('products', 'game_region_label')) {
                $table->json('game_region_label')->nullable()->after('game_player_id_label');
            }

            if (! Schema::hasColumn('products', 'game_server_label')) {
                $table->json('game_server_label')->nullable()->after('game_region_label');
            }

            if (! Schema::hasColumn('products', 'game_topup_instructions')) {
                $table->json('game_topup_instructions')->nullable()->after('game_server_label');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (! Schema::hasColumn('product_variants', 'provider_sku')) {
                $table->string('provider_sku')->nullable()->after('sku');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'provider_sku')) {
                $table->dropColumn('provider_sku');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'game_title',
                'game_currency_name',
                'game_delivery_mode',
                'game_provider',
                'game_provider_sku',
                'game_requires_player_id',
                'game_requires_region',
                'game_requires_server',
                'game_can_validate_player',
                'game_player_id_label',
                'game_region_label',
                'game_server_label',
                'game_topup_instructions',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('storefront_settings', function (Blueprint $table) {
            if (Schema::hasColumn('storefront_settings', 'enable_game_topups')) {
                $table->dropColumn('enable_game_topups');
            }
        });
    }
};
