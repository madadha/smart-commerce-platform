<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table): void {
            $jsonColumns = [
                'cookie_consent_text',
                'cookie_consent_button_text',
                'cookie_consent_privacy_text',
            ];

            foreach ($jsonColumns as $column) {
                if (! Schema::hasColumn('storefront_settings', $column)) {
                    $table->json($column)->nullable();
                }
            }

            foreach ([
                'cookie_consent_privacy_url',
                'cookie_consent_storage_key',
                'cookie_consent_background_color',
                'cookie_consent_button_color',
            ] as $column) {
                if (! Schema::hasColumn('storefront_settings', $column)) {
                    $table->string($column)->nullable();
                }
            }

            if (! Schema::hasColumn('storefront_settings', 'show_cookie_consent')) {
                $table->boolean('show_cookie_consent')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('storefront_settings', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('storefront_settings', 'cookie_consent_text') ? 'cookie_consent_text' : null,
                Schema::hasColumn('storefront_settings', 'cookie_consent_button_text') ? 'cookie_consent_button_text' : null,
                Schema::hasColumn('storefront_settings', 'cookie_consent_privacy_text') ? 'cookie_consent_privacy_text' : null,
                Schema::hasColumn('storefront_settings', 'cookie_consent_privacy_url') ? 'cookie_consent_privacy_url' : null,
                Schema::hasColumn('storefront_settings', 'cookie_consent_storage_key') ? 'cookie_consent_storage_key' : null,
                Schema::hasColumn('storefront_settings', 'cookie_consent_background_color') ? 'cookie_consent_background_color' : null,
                Schema::hasColumn('storefront_settings', 'cookie_consent_button_color') ? 'cookie_consent_button_color' : null,
                Schema::hasColumn('storefront_settings', 'show_cookie_consent') ? 'show_cookie_consent' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
