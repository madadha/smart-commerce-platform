<?php

namespace App\Payments;

use App\Contracts\Payments\PaymentGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function gatewayForMethod(string $method): PaymentGateway
    {
        $methodConfig = config("payments.methods.{$method}");

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
        return collect(config('payments.methods', []))
            ->filter(fn ($method): bool => is_array($method) && ($method['enabled'] ?? false))
            ->all();
    }
}
