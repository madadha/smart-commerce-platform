<?php

namespace App\Services\Shipping;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    public function createForOrder(Order $order, array $attributes = []): Shipment
    {
        return DB::transaction(function () use ($order, $attributes) {
            $shipment = $order->shipments()->create(array_merge([
                'shipping_method_id' => $order->shipping_method_id,
                'status' => ShipmentStatus::Pending,
                'carrier_name' => $order->shippingMethod?->external_company_name,
                'shipping_address' => [
                    'country_id' => $order->shipping_country_id,
                    'city' => $order->shipping_city ?? null,
                    'address' => is_array($order->shipping_address) ? $order->shipping_address : ($order->shipping_address ?? null),
                ],
                'weight' => $this->resolveShipmentWeight($order),
                'shipping_cost' => (float) ($order->shipping_total ?? 0),
            ], $attributes));

            return $shipment->refresh();
        });
    }

    private function resolveShipmentWeight(Order $order): float
    {
        if ($order->shipping_weight !== null) {
            return max((float) $order->shipping_weight, 0);
        }

        $order->loadMissing(['items.product', 'items.productVariant']);

        $weight = $order->items->sum(function ($item): float {
            $itemWeight = $item->productVariant?->weight ?? $item->product?->weight ?? 0;

            return (float) $itemWeight * max((int) $item->quantity, 1);
        });

        return max((float) $weight, 0);
    }

    public function transition(Shipment $shipment, ShipmentStatus $status, ?string $description = null, ?string $location = null): Shipment
    {
        return DB::transaction(function () use ($shipment, $status, $description, $location) {
            $timestamps = match ($status) {
                ShipmentStatus::Shipped, ShipmentStatus::InTransit => ['shipped_at' => $shipment->shipped_at ?? now()],
                ShipmentStatus::Delivered => ['delivered_at' => now()],
                ShipmentStatus::Failed => ['failed_at' => now()],
                ShipmentStatus::Cancelled => ['cancelled_at' => now()],
                default => [],
            };
            $shipment->forceFill(array_merge(['status' => $status], $timestamps))->save();
            if ($description || $location) {
                $shipment->events()->latest('id')->first()?->update(['description' => $description, 'location' => $location]);
            }

            if (in_array($status, [ShipmentStatus::Shipped, ShipmentStatus::InTransit, ShipmentStatus::OutForDelivery], true)) {
                $shipment->order->forceFill(['status' => OrderStatus::Processing])->saveQuietly();
            } elseif ($status === ShipmentStatus::Delivered && $shipment->order->shipments()->where('status', '!=', ShipmentStatus::Delivered->value)->doesntExist()) {
                $shipment->order->forceFill(['status' => OrderStatus::Completed, 'completed_at' => now()])->saveQuietly();
            }

            return $shipment->refresh();
        });
    }
}
