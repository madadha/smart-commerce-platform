<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => ShipmentStatus::class,
        'shipping_address' => 'array',
        'weight' => 'decimal:3',
        'shipping_cost' => 'decimal:2',
        'estimated_delivery_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment): void {
            $shipment->shipment_number ??= 'SHP-'.now()->format('Ymd').'-'.strtoupper(str()->random(8));
        });

        static::created(function (Shipment $shipment): void {
            $shipment->events()->create([
                'user_id' => auth()->id(), 'status' => $shipment->status,
                'title' => 'Shipment created', 'occurred_at' => now(), 'is_customer_visible' => true,
            ]);
        });

        static::updating(function (Shipment $shipment): void {
            if (! $shipment->isDirty('status')) {
                return;
            }
            $status = $shipment->status instanceof ShipmentStatus ? $shipment->status : ShipmentStatus::from($shipment->status);
            if (in_array($status, [ShipmentStatus::Shipped, ShipmentStatus::InTransit], true)) {
                $shipment->shipped_at ??= now();
            }
            if ($status === ShipmentStatus::Delivered) {
                $shipment->delivered_at ??= now();
            }
            if ($status === ShipmentStatus::Failed) {
                $shipment->failed_at ??= now();
            }
            if ($status === ShipmentStatus::Cancelled) {
                $shipment->cancelled_at ??= now();
            }
        });

        static::updated(function (Shipment $shipment): void {
            if (! $shipment->wasChanged('status')) {
                return;
            }
            $status = $shipment->status;
            $shipment->events()->create([
                'user_id' => auth()->id(), 'status' => $status, 'title' => $status->label(),
                'occurred_at' => now(), 'is_customer_visible' => true,
            ]);
            if (in_array($status, [ShipmentStatus::Shipped, ShipmentStatus::InTransit, ShipmentStatus::OutForDelivery], true)) {
                $shipment->order->forceFill(['status' => OrderStatus::Processing])->saveQuietly();
            } elseif ($status === ShipmentStatus::Delivered && $shipment->order->shipments()->where('status', '!=', ShipmentStatus::Delivered->value)->doesntExist()) {
                $shipment->order->forceFill(['status' => OrderStatus::Completed, 'completed_at' => now()])->saveQuietly();
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class)->orderByDesc('occurred_at')->orderByDesc('id');
    }
}
