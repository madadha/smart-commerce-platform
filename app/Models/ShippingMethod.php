<?php

namespace App\Models;

use App\Enums\ShippingMethodType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'country_id',
        'currency_id',
        'base_cost',
        'free_shipping_min_total',
        'min_delivery_days',
        'max_delivery_days',
        'external_company_name',
        'external_company_phone',
        'external_company_website',
        'allowed_cities',
        'excluded_cities',
        'requires_address',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'type' => ShippingMethodType::class,
        'base_cost' => 'decimal:2',
        'free_shipping_min_total' => 'decimal:2',
        'min_delivery_days' => 'integer',
        'max_delivery_days' => 'integer',
        'allowed_cities' => 'array',
        'excluded_cities' => 'array',
        'requires_address' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (ShippingMethod $method): void {
            if (blank($method->slug)) {
                $method->slug = Str::slug(
                    $method->getName('en') ?: $method->getName('ar') ?: 'shipping-method'
                );
            }

            if ($method->type instanceof ShippingMethodType) {
                $method->requires_address = $method->type->requiresAddress();
            }
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getName(string $locale = 'ar'): string
    {
        $name = $this->name;

        if (is_string($name)) {
            $decoded = json_decode($name, true);
            $name = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($name)) {
            return 'Shipping Method';
        }

        return $name[$locale]
            ?? $name['en']
            ?? $name['ar']
            ?? 'Shipping Method';
    }

    public function getDescription(string $locale = 'ar'): ?string
    {
        $description = $this->description;

        if (is_string($description)) {
            $decoded = json_decode($description, true);
            $description = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($description)) {
            return null;
        }

        return $description[$locale]
            ?? $description['en']
            ?? $description['ar']
            ?? null;
    }

    public function calculateCost(float $orderTotal): float
    {
        if (
            $this->free_shipping_min_total !== null
            && $orderTotal >= (float) $this->free_shipping_min_total
        ) {
            return 0;
        }

        return (float) $this->base_cost;
    }

    public function getDeliveryEstimate(): string
    {
        if (! $this->min_delivery_days && ! $this->max_delivery_days) {
            return '-';
        }

        if ($this->min_delivery_days === $this->max_delivery_days) {
            return $this->min_delivery_days . ' day(s)';
        }

        return $this->min_delivery_days . ' - ' . $this->max_delivery_days . ' days';
    }


    public function orders(): HasMany
{
    return $this->hasMany(Order::class, 'shipping_method_id');
}
}