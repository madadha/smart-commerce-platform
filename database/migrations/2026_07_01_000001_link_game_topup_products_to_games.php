<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'game_id')) {
                $table->foreignId('game_id')
                    ->nullable()
                    ->after('youtube_enabled')
                    ->constrained('games')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('products', 'game_server_options')) {
                $table->json('game_server_options')
                    ->nullable()
                    ->after('game_requires_server');
            }
        });

        if (! Schema::hasTable('game_product_region')) {
            Schema::create('game_product_region', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('game_region_id')->constrained()->cascadeOnDelete();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['product_id', 'game_region_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('game_product_region');

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'game_id')) {
                $table->dropConstrainedForeignId('game_id');
            }

            if (Schema::hasColumn('products', 'game_server_options')) {
                $table->dropColumn('game_server_options');
            }
        });
    }
};
