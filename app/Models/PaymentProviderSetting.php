<?php

namespace App\Models;

use App\Contracts\Payments\PaymentGateway;
use Illuminate\Database\Eloquent\Model;

class PaymentProviderSetting extends Model
{
    protected $fillable = [
        'provider',
        'display_name',
        'description',
        'is_enabled',
        'mode',
        'sandbox_credentials',
        'live_credentials',
        'supported_currencies',
        'connection_status',
        'last_tested_at',
        'last_error',
        'sort_order',
    ];

    protected $casts = [
        'display_name' => 'array',
        'description' => 'array',
        'is_enabled' => 'boolean',
        'sandbox_credentials' => 'encrypted:array',
        'live_credentials' => 'encrypted:array',
        'supported_currencies' => 'array',
        'last_tested_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function activeCredentials(): array
    {
        return $this->mode === 'live'
            ? ($this->live_credentials ?? [])
            : ($this->sandbox_credentials ?? []);
    }

    public function isCheckoutReady(): bool
    {
        $gatewayClass = config("payments.gateways.{$this->provider}");

        return $this->is_enabled
            && $this->connection_status === 'verified'
            && is_string($gatewayClass)
            && is_a($gatewayClass, PaymentGateway::class, true)
            && $this->hasRequiredCredentials();
    }

    public function hasRequiredCredentials(): bool
    {
        $credentials = $this->activeCredentials();
        $required = match ($this->provider) {
            'payplus' => ['api_key', 'secret_key', 'payment_page_uid', 'terminal_uid'],
            'paypal' => ['client_id', 'client_secret', 'webhook_id'],
            'stripe' => ['publishable_key', 'secret_key', 'webhook_secret'],
            'paddle' => ['client_token', 'api_key', 'webhook_secret'],
            default => [],
        };

        return $required !== []
            && collect($required)->every(fn (string $key): bool => filled($credentials[$key] ?? null));
    }

    public function getDisplayName(string $locale = 'en'): string
    {
        return $this->display_name[$locale]
            ?? $this->display_name['en']
            ?? ucfirst($this->provider);
    }
}
