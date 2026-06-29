<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('storefront_settings', 'body_font_family')) {
                $table->string('body_font_family')->nullable()->after('muted_text_color');
            }

            if (! Schema::hasColumn('storefront_settings', 'heading_font_family')) {
                $table->string('heading_font_family')->nullable()->after('body_font_family');
            }
        });
    }

    public function down(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('storefront_settings', 'body_font_family') ? 'body_font_family' : null,
                Schema::hasColumn('storefront_settings', 'heading_font_family') ? 'heading_font_family' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
