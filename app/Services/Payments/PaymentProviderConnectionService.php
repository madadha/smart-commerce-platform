<?php

namespace App\Services\Payments;

use App\Models\PaymentProviderSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class PaymentProviderConnectionService
{
    public function test(PaymentProviderSetting $settings): bool
    {
        try {
            if (! $settings->hasRequiredCredentials()) {
                throw new RuntimeException('Complete all required credentials before testing the connection.');
            }

            match ($settings->provider) {
                'payplus' => $this->testPayPlus($settings),
                default => throw new RuntimeException('This provider connector is not installed yet.'),
            };

            $settings->forceFill([
                'connection_status' => 'verified',
                'last_tested_at' => now(),
                'last_error' => null,
            ])->save();

            return true;
        } catch (Throwable $exception) {
            $settings->forceFill([
                'connection_status' => 'failed',
                'last_tested_at' => now(),
                'last_error' => $exception->getMessage(),
            ])->save();

            report($exception);

            return false;
        }
    }

    private function testPayPlus(PaymentProviderSetting $settings): void
    {
        $credentials = $settings->activeCredentials();
        $baseUrl = $settings->mode === 'live'
            ? 'https://restapi.payplus.co.il/api/v1.0'
            : 'https://restapidev.payplus.co.il/api/v1.0';

        Http::acceptJson()
            ->withHeaders([
                'api-key' => $credentials['api_key'],
                'secret-key' => $credentials['secret_key'],
            ])
            ->timeout(15)
            ->get($baseUrl.'/PaymentPages/list/', [
                'terminal_uid' => $credentials['terminal_uid'],
                'take' => 1,
            ])
            ->throw();
    }
}
