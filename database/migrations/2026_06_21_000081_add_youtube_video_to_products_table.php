<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('main_image');
            }

            if (! Schema::hasColumn('products', 'youtube_enabled')) {
                $table->boolean('youtube_enabled')->default(false)->after('youtube_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('products', 'youtube_url') ? 'youtube_url' : null,
                Schema::hasColumn('products', 'youtube_enabled') ? 'youtube_enabled' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
