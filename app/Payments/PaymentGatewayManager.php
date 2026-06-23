<?php

namespace App\Payments;

use App\Contracts\Payments\PaymentGateway;
use App\Models\PaymentProviderSetting;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function gatewayForMethod(string $method): PaymentGateway
    {
        $methodConfig = $this->methodConfig($method);

        if (! is_array($methodConfig) || ! ($methodConfig['enabled'] ?? false)) {
            throw new InvalidArgumentException("Payment method [{$method}] is not enabled.");
        }

        $provider = (string) ($methodConfig['provider'] ?? '');
        $gatewayClass = config("payments.gateways.{$provider}");

        if (! is_string($gatewayClass) || ! is_a($gatewayClass, PaymentGateway::class, true)) {
            throw new InvalidArgumentException("Payment gateway [{$provider}] is not configured.");
        }

        return app($gatewayClass);
    }

    public function enabledMethods(): array
    {
        $methods = collect(config('payments.methods', []))
            ->filter(fn ($method): bool => is_array($method) && ($method['enabled'] ?? false))
            ->all();

        if (! Schema::hasTable('payment_provider_settings')) {
            return $methods;
        }

        PaymentProviderSetting::query()
            ->where('is_enabled', true)
            ->orderBy('sort_order')
            ->get()
            ->filter->isCheckoutReady()
            ->each(function (PaymentProviderSetting $setting) use (&$methods): void {
                $config = config("payments.methods.{$setting->provider}", []);
                $methods[$setting->provider] = array_replace($config, [
                    'enabled' => true,
                    'provider' => $setting->provider,
                    'display_name' => $setting->display_name,
                    'sort_order' => $setting->sort_order,
                ]);
            });

        return collect($methods)
            ->sortBy('sort_order')
            ->all();
    }

    public function settingsFor(string $provider): ?PaymentProviderSetting
    {
        if (! Schema::hasTable('payment_provider_settings')) {
            return null;
        }

        return PaymentProviderSetting::query()->where('provider', $provider)->first();
    }

    private function methodConfig(string $method): mixed
    {
        $config = config("payments.methods.{$method}");

        if (is_array($config) && ($config['enabled'] ?? false)) {
            return $config;
        }

        $settings = $this->settingsFor($method);

        if (! $settings?->isCheckoutReady()) {
            return $config;
        }

        return array_replace(is_array($config) ? $config : [], [
            'enabled' => true,
            'provider' => $method,
        ]);
    }
}
