<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_provider_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->unique();
            $table->json('display_name');
            $table->json('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->string('mode')->default('sandbox');
            $table->longText('sandbox_credentials')->nullable();
            $table->longText('live_credentials')->nullable();
            $table->json('supported_currencies')->nullable();
            $table->string('connection_status')->default('not_configured');
            $table->dateTime('last_tested_at')->nullable();
            $table->text('last_error')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
            $table->index(['provider', 'mode']);
        });

        $providers = [
            ['provider' => 'payplus', 'name' => 'PayPlus', 'description' => 'Local card payment provider for the primary market.', 'currencies' => ['ILS', 'USD', 'EUR'], 'sort_order' => 10],
            ['provider' => 'paypal', 'name' => 'PayPal', 'description' => 'PayPal wallet and international checkout.', 'currencies' => ['ILS', 'USD', 'EUR', 'GBP'], 'sort_order' => 20],
            ['provider' => 'stripe', 'name' => 'Stripe', 'description' => 'Global card and wallet payments for supported business countries.', 'currencies' => ['ILS', 'USD', 'EUR', 'GBP'], 'sort_order' => 30],
            ['provider' => 'paddle', 'name' => 'Paddle', 'description' => 'Merchant of Record for digital products and subscriptions.', 'currencies' => ['USD', 'EUR', 'GBP'], 'sort_order' => 40],
        ];

        foreach ($providers as $provider) {
            DB::table('payment_provider_settings')->insert([
                'provider' => $provider['provider'],
                'display_name' => json_encode([
                    'ar' => $provider['name'],
                    'he' => $provider['name'],
                    'en' => $provider['name'],
                ], JSON_UNESCAPED_UNICODE),
                'description' => json_encode([
                    'ar' => $provider['description'],
                    'he' => $provider['description'],
                    'en' => $provider['description'],
                ], JSON_UNESCAPED_UNICODE),
                'is_enabled' => false,
                'mode' => 'sandbox',
                'supported_currencies' => json_encode($provider['currencies']),
                'connection_status' => 'not_configured',
                'sort_order' => $provider['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_provider_settings');
    }
};
