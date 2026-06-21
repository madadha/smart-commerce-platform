<?php

namespace App\Services\Checkout;

use App\Enums\DigitalCodeStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductDigitalCode;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutInventoryService
{
    public function handleOrderInventory(Order $order): void
    {
        $this->reserveOrderInventory($order);
    }

    public function reserveOrderInventory(Order $order): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $orderItem) {
            if ($orderItem->inventory_status === 'reserved' || $orderItem->inventory_status === 'fulfilled') {
                continue;
            }

            $quantity = max((int) $orderItem->quantity, 1);
            $product = Product::query()->lockForUpdate()->find($orderItem->product_id);

            if (! $product) {
                throw new RuntimeException('Product not found for order item: '.$orderItem->id);
            }

            if ($this->usesDigitalCodes($orderItem, $product)) {
                $this->reserveDigitalCodes($order, $orderItem, $product, $quantity);
            } elseif (! $this->isNonStockProduct($product)) {
                $this->reservePhysicalStock($orderItem, $product, $quantity);
            }

            $orderItem->forceFill([
                'inventory_status' => 'reserved',
                'inventory_reserved_at' => now(),
                'inventory_fulfilled_at' => null,
                'inventory_released_at' => null,
            ])->save();
        }
    }

    public function fulfillOrderInventory(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            if ($order->items()->where('inventory_status', 'released')->exists()) {
                $this->reserveOrderInventory($order);
            }

            $order->load('items');

            foreach ($order->items as $orderItem) {
                if ($orderItem->inventory_status === 'fulfilled') {
                    continue;
                }

                if ($orderItem->inventory_status !== 'reserved') {
                    throw new RuntimeException('Order inventory is not reserved for item: '.$orderItem->id);
                }

                ProductDigitalCode::query()
                    ->where('order_item_id', $orderItem->id)
                    ->where('status', DigitalCodeStatus::Reserved->value)
                    ->lockForUpdate()
                    ->update([
                        'status' => DigitalCodeStatus::Sold->value,
                        'sold_to' => $order->user_id,
                        'sold_at' => now(),
                    ]);

                $orderItem->forceFill([
                    'inventory_status' => 'fulfilled',
                    'inventory_fulfilled_at' => now(),
                ])->save();
            }
        });
    }

    public function releaseOrderInventory(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $order->load('items');

            foreach ($order->items as $orderItem) {
                if ($orderItem->inventory_status !== 'reserved') {
                    continue;
                }

                $product = Product::query()->lockForUpdate()->find($orderItem->product_id);

                if ($product && $this->usesDigitalCodes($orderItem, $product)) {
                    $this->releaseDigitalCodes($orderItem);
                } elseif ($product && ! $this->isNonStockProduct($product)) {
                    $this->releasePhysicalStock($orderItem, $product);
                }

                $orderItem->forceFill([
                    'inventory_status' => 'released',
                    'inventory_released_at' => now(),
                ])->save();
            }
        });
    }

    private function reservePhysicalStock(OrderItem $orderItem, Product $product, int $quantity): void
    {
        if ($orderItem->product_variant_id) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->find($orderItem->product_variant_id);

            if (! $variant || ! $variant->is_active) {
                throw new RuntimeException('Selected product variant is not available.');
            }

            if ($variant->track_stock) {
                $this->deductStock($variant, $quantity, 'variant');
            }

            return;
        }

        if ($product->track_stock) {
            $this->deductStock($product, $quantity, 'product');
        }
    }

    private function releasePhysicalStock(OrderItem $orderItem, Product $product): void
    {
        $quantity = max((int) $orderItem->quantity, 1);

        if ($orderItem->product_variant_id) {
            $variant = ProductVariant::query()->lockForUpdate()->find($orderItem->product_variant_id);

            if ($variant && $variant->track_stock) {
                $variant->increment('stock_quantity', $quantity);
            }

            return;
        }

        if ($product->track_stock) {
            $product->increment('stock_quantity', $quantity);
        }
    }

    private function deductStock(Product|ProductVariant $stockable, int $quantity, string $label): void
    {
        $currentStock = (int) $stockable->stock_quantity;

        if ($currentStock < $quantity) {
            throw new RuntimeException('Insufficient stock for '.$label.': '.($stockable->sku ?? $stockable->id));
        }

        $stockable->forceFill(['stock_quantity' => $currentStock - $quantity])->save();
    }

    private function reserveDigitalCodes(Order $order, OrderItem $orderItem, Product $product, int $quantity): void
    {
        $codes = ProductDigitalCode::query()
            ->where('product_id', $product->id)
            ->when($orderItem->product_variant_id, function ($query, $variantId) {
                $query->where(function ($query) use ($variantId) {
                    $query->where('product_variant_id', $variantId)
                        ->orWhereNull('product_variant_id');
                });
            })
            ->where('status', DigitalCodeStatus::Available->value)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->lockForUpdate()
            ->limit($quantity)
            ->get();

        if ($codes->count() < $quantity) {
            throw new RuntimeException('Not enough digital codes available for product: '.($product->sku ?? $product->id));
        }

        foreach ($codes as $code) {
            $code->forceFill([
                'status' => DigitalCodeStatus::Reserved,
                'reserved_by' => $order->user_id,
                'reserved_at' => now(),
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'sold_to' => null,
                'sold_at' => null,
            ])->save();
        }

        $orderItem->forceFill(['digital_code_id' => $codes->first()->id])->save();
    }

    private function releaseDigitalCodes(OrderItem $orderItem): void
    {
        ProductDigitalCode::query()
            ->where('order_item_id', $orderItem->id)
            ->where('status', DigitalCodeStatus::Reserved->value)
            ->lockForUpdate()
            ->update([
                'status' => DigitalCodeStatus::Available->value,
                'reserved_by' => null,
                'reserved_at' => null,
                'order_id' => null,
                'order_item_id' => null,
            ]);

        $orderItem->forceFill(['digital_code_id' => null])->save();
    }

    private function usesDigitalCodes(OrderItem $orderItem, Product $product): bool
    {
        $type = $product->product_type;

        if ($type instanceof \BackedEnum) {
            $type = $type->value;
        }

        return in_array((string) $type, ['digital', 'digital_code', 'digital_card'], true)
            || $orderItem->item_type === 'digital_code';
    }

    private function isNonStockProduct(Product $product): bool
    {
        $type = $product->product_type;

        if ($type instanceof \BackedEnum) {
            $type = $type->value;
        }

        return in_array((string) $type, ['digital_file', 'service', 'subscription'], true);
    }
}
