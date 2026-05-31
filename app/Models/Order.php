<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod as ShippingMethodEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'user_id',
        'currency_id',
        'shipping_method_id',
        'status',
        'payment_status',
        'payment_method',
        'shipping_method',
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_total',
        'grand_total',
        'paid_total',
        'billing_address',
        'shipping_address',
        'customer_notes',
        'internal_notes',
        'ordered_at',
        'paid_at',
        'completed_at',
        'cancelled_at',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'shipping_method' => ShippingMethodEnum::class,
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'ordered_at' => 'datetime',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (blank($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }

            if (blank($order->ordered_at)) {
                $order->ordered_at = now();
            }
        });

        static::saving(function (Order $order): void {
            if ($order->shippingMethod) {
                $order->shipping_method = $order->shippingMethod->type?->value ?? $order->shipping_method;
                $order->shipping_total = $order->shippingMethod->calculateCost((float) $order->subtotal);
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . now()->format('Ymd') . '-';

        $count = self::query()
            ->whereDate('created_at', today())
            ->count() + 1;

        return $prefix . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)
            ->orderBy('id');
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');

        $this->subtotal = $subtotal;

        if ($this->shippingMethod) {
            $this->shipping_total = $this->shippingMethod->calculateCost((float) $subtotal);
            $this->shipping_method = $this->shippingMethod->type?->value ?? $this->shipping_method;
        }

        $this->grand_total = max(
            0,
            (float) $this->subtotal
            - (float) $this->discount_total
            + (float) $this->tax_total
            + (float) $this->shipping_total
        );

        $this->saveQuietly();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function isCompleted(): bool
    {
        return $this->status === OrderStatus::Completed;
    }
}