<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod as ShippingMethodEnum;
use App\Services\Pricing\CommerceTotalsCalculator;
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
        'shipping_country_id',
        'coupon_id',
        'coupon_code',
        'coupon_discount_type',
        'coupon_discount_value',
        'status',
        'payment_status',
        'payment_method',
        'locale',
        'shipping_method',
        'subtotal',
        'discount_total',
        'tax_total',
        'shipping_total',
        'shipping_weight',
        'shipping_min_delivery_days',
        'shipping_max_delivery_days',
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
        'shipping_weight' => 'decimal:3',
        'grand_total' => 'decimal:2',
        'paid_total' => 'decimal:2',
        'coupon_discount_value' => 'decimal:2',
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
            if ($order->coupon) {
                $order->coupon_code = $order->coupon->code;
                $order->coupon_discount_type = $order->coupon->discount_type?->value ?? null;
                $order->coupon_discount_value = $order->coupon->discount_value;
            }

            if ($order->shippingMethod) {
                $order->shipping_method = $order->shippingMethod->type?->value ?? $order->shipping_method;
            }

            $totals = app(CommerceTotalsCalculator::class)->calculate(
                subtotal: (float) $order->subtotal,
                taxTotal: (float) $order->tax_total,
                coupon: $order->coupon,
                shippingMethod: $order->shippingMethod,
                shippingTotalOverride: (float) $order->shipping_total,
            );

            $order->discount_total = $totals['discountTotal'];
            $order->shipping_total = $totals['shippingTotal'];
            $order->grand_total = $totals['grandTotal'];
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';

        $count = self::query()
            ->whereDate('created_at', today())
            ->count() + 1;

        return $prefix.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
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

    public function shippingCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'shipping_country_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class)->orderByDesc('id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)
            ->orderBy('id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)
            ->with('user')
            ->orderByDesc('changed_at')
            ->orderByDesc('id');
    }

    public function orderNotes(): HasMany
    {
        return $this->hasMany(OrderNote::class)
            ->with('user')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(OrderAttachment::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function orderAttachments(): HasMany
    {
        return $this->attachments();
    }

    public function orderActivities(): HasMany
    {
        return $this->hasMany(OrderActivity::class)
            ->with('user')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');
    }

    public function orderTasks(): HasMany
    {
        return $this->hasMany(OrderTask::class)
            ->with(['user', 'assignedTo'])
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'done', 'cancelled')")
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->orderByDesc('id');
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('line_total');

        $totals = app(CommerceTotalsCalculator::class)->calculate(
            subtotal: (float) $subtotal,
            taxTotal: (float) $this->tax_total,
            coupon: $this->coupon,
            shippingMethod: $this->shippingMethod,
            shippingTotalOverride: (float) $this->shipping_total,
        );

        $this->subtotal = $totals['subtotal'];
        $this->discount_total = $totals['discountTotal'];
        $this->shipping_total = $totals['shippingTotal'];
        $this->grand_total = $totals['grandTotal'];

        if ($this->shippingMethod) {
            $this->shipping_method = $this->shippingMethod->type?->value ?? $this->shipping_method;
        }

        if ($this->coupon) {
            $this->coupon_code = $this->coupon->code;
            $this->coupon_discount_type = $this->coupon->discount_type?->value ?? null;
            $this->coupon_discount_value = $this->coupon->discount_value;

        }

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
