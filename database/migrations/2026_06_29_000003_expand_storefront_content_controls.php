<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $jsonColumns = [
                'categories_section_title',
                'categories_section_subtitle',
                'featured_section_title',
                'featured_section_subtitle',
                'latest_section_title',
                'latest_section_subtitle',
                'brands_section_title',
                'brands_section_subtitle',
                'footer_rights_text',
            ];

            foreach ($jsonColumns as $column) {
                if (! Schema::hasColumn('storefront_settings', $column)) {
                    $table->json($column)->nullable();
                }
            }

            foreach (['facebook_icon', 'instagram_icon', 'tiktok_icon', 'youtube_icon', 'whatsapp_floating_icon'] as $column) {
                if (! Schema::hasColumn('storefront_settings', $column)) {
                    $table->string($column)->nullable();
                }
            }

            if (! Schema::hasColumn('storefront_settings', 'show_floating_whatsapp')) {
                $table->boolean('show_floating_whatsapp')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('storefront_settings', 'categories_section_title') ? 'categories_section_title' : null,
                Schema::hasColumn('storefront_settings', 'categories_section_subtitle') ? 'categories_section_subtitle' : null,
                Schema::hasColumn('storefront_settings', 'featured_section_title') ? 'featured_section_title' : null,
                Schema::hasColumn('storefront_settings', 'featured_section_subtitle') ? 'featured_section_subtitle' : null,
                Schema::hasColumn('storefront_settings', 'latest_section_title') ? 'latest_section_title' : null,
                Schema::hasColumn('storefront_settings', 'latest_section_subtitle') ? 'latest_section_subtitle' : null,
                Schema::hasColumn('storefront_settings', 'brands_section_title') ? 'brands_section_title' : null,
                Schema::hasColumn('storefront_settings', 'brands_section_subtitle') ? 'brands_section_subtitle' : null,
                Schema::hasColumn('storefront_settings', 'footer_rights_text') ? 'footer_rights_text' : null,
                Schema::hasColumn('storefront_settings', 'facebook_icon') ? 'facebook_icon' : null,
                Schema::hasColumn('storefront_settings', 'instagram_icon') ? 'instagram_icon' : null,
                Schema::hasColumn('storefront_settings', 'tiktok_icon') ? 'tiktok_icon' : null,
                Schema::hasColumn('storefront_settings', 'youtube_icon') ? 'youtube_icon' : null,
                Schema::hasColumn('storefront_settings', 'whatsapp_floating_icon') ? 'whatsapp_floating_icon' : null,
                Schema::hasColumn('storefront_settings', 'show_floating_whatsapp') ? 'show_floating_whatsapp' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
