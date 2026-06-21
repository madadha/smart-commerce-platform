<?php

namespace App\Models;

use App\Enums\CartStatus;
use App\Services\Pricing\CommerceTotalsCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'cart_number',
        'user_id',
        'customer_id',
        'currency_id',
        'shipping_method_id',
        'coupon_id',
        'coupon_code',
        'coupon_discount_type',
        'coupon_discount_value',
        'status',
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_total',
        'grand_total',
        'customer_notes',
        'internal_notes',
        'converted_at',
        'abandoned_at',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'status' => CartStatus::class,
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'coupon_discount_value' => 'decimal:2',
        'converted_at' => 'datetime',
        'abandoned_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Cart $cart): void {
            if (blank($cart->cart_number)) {
                $cart->cart_number = self::generateCartNumber();
            }
        });

        static::saving(function (Cart $cart): void {
            if ($cart->coupon) {
                $cart->coupon_code = $cart->coupon->code;
                $cart->coupon_discount_type = $cart->coupon->discount_type?->value ?? null;
                $cart->coupon_discount_value = $cart->coupon->discount_value;
            }

            $totals = app(CommerceTotalsCalculator::class)->calculate(
                subtotal: (float) $cart->subtotal,
                taxTotal: (float) $cart->tax_total,
                coupon: $cart->coupon,
                shippingMethod: $cart->shippingMethod,
            );

            $cart->discount_total = $totals['discountTotal'];
            $cart->shipping_total = $totals['shippingTotal'];
            $cart->grand_total = $totals['grandTotal'];
        });
    }

    public static function generateCartNumber(): string
    {
        $prefix = 'CART-'.now()->format('Ymd').'-';

        $count = self::query()
            ->whereDate('created_at', today())
            ->count() + 1;

        return $prefix.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');

        $totals = app(CommerceTotalsCalculator::class)->calculate(
            subtotal: (float) $subtotal,
            taxTotal: (float) $this->tax_total,
            coupon: $this->coupon,
            shippingMethod: $this->shippingMethod,
        );

        $this->subtotal = $totals['subtotal'];
        $this->discount_total = $totals['discountTotal'];
        $this->shipping_total = $totals['shippingTotal'];
        $this->grand_total = $totals['grandTotal'];

        if ($this->coupon) {
            $this->coupon_code = $this->coupon->code;
            $this->coupon_discount_type = $this->coupon->discount_type?->value ?? null;
            $this->coupon_discount_value = $this->coupon->discount_value;

        }

        $this->saveQuietly();
    }

    public function isActive(): bool
    {
        return $this->status === CartStatus::Active && $this->is_active;
    }

    public function isConverted(): bool
    {
        return $this->status === CartStatus::Converted;
    }
}
