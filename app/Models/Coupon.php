<?php

namespace App\Models;

use App\Enums\CouponDiscountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'currency_id',
        'minimum_order_total',
        'maximum_discount_amount',
        'usage_limit',
        'usage_limit_per_customer',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
        'discount_type' => CouponDiscountType::class,
        'discount_value' => 'decimal:2',
        'minimum_order_total' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Coupon $coupon): void {
            $coupon->code = strtoupper(trim((string) $coupon->code));
        });
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getName(string $locale = 'ar'): string
    {
        $name = $this->name;

        if (is_string($name)) {
            $decoded = json_decode($name, true);
            $name = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($name)) {
            return $this->code;
        }

        return $name[$locale]
            ?? $name['en']
            ?? $name['ar']
            ?? $this->code;
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

    public function isValidForAmount(float $orderTotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($this->minimum_order_total !== null && $orderTotal < (float) $this->minimum_order_total) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $orderTotal, float $shippingTotal = 0): float
    {
        if (! $this->isValidForAmount($orderTotal)) {
            return 0;
        }

        $discount = match ($this->discount_type) {
            CouponDiscountType::Percentage => $orderTotal * ((float) $this->discount_value / 100),
            CouponDiscountType::FixedAmount => (float) $this->discount_value,
            CouponDiscountType::FreeShipping => $shippingTotal,
        };

        if ($this->maximum_discount_amount !== null) {
            $discount = min($discount, (float) $this->maximum_discount_amount);
        }

        return round(min($discount, $orderTotal + $shippingTotal), 2);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}