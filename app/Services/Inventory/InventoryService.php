<?php

namespace App\Services\Inventory;

use App\Enums\DigitalCodeStatus;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductDigitalCode;
use App\Models\ProductVariant;
use RuntimeException;

class InventoryService
{
    public function processOrderItem(OrderItem $orderItem): void
    {
        if ($orderItem->item_type === 'digital_code') {
            $this->assignDigitalCodeToOrderItem($orderItem);
            return;
        }

        $this->deductPhysicalStock($orderItem);
    }

    private function deductPhysicalStock(OrderItem $orderItem): void
    {
        $quantity = (int) $orderItem->quantity;

        if ($quantity <= 0) {
            throw new RuntimeException('Invalid quantity for order item.');
        }

        if ($orderItem->productVariant) {
            $this->deductVariantStock($orderItem->productVariant, $quantity);
            return;
        }

        if ($orderItem->product) {
            $this->deductProductStock($orderItem->product, $quantity);
        }
    }

    private function deductProductStock(Product $product, int $quantity): void
    {
        if (! $product->track_stock) {
            return;
        }

        if ((int) $product->stock_quantity < $quantity) {
            throw new RuntimeException(
                'Not enough stock for product: ' . $product->getName('ar')
            );
        }

        $product->decrement('stock_quantity', $quantity);

        if ((int) $product->fresh()->stock_quantity <= 0) {
            $product->update([
                'status' => \App\Enums\ProductStatus::OutOfStock,
            ]);
        }
    }

    private function deductVariantStock(ProductVariant $variant, int $quantity): void
    {
        if (! $variant->track_stock) {
            return;
        }

        if ((int) $variant->stock_quantity < $quantity) {
            throw new RuntimeException(
                'Not enough stock for variant: ' . $variant->getName('ar')
            );
        }

        $variant->decrement('stock_quantity', $quantity);
    }

    private function assignDigitalCodeToOrderItem(OrderItem $orderItem): void
    {
        if ($orderItem->digital_code_id) {
            return;
        }

        $productId = $orderItem->product_id;
        $variantId = $orderItem->product_variant_id;

        if (! $productId) {
            throw new RuntimeException('Digital code item must have product_id.');
        }

        $codeQuery = ProductDigitalCode::query()
            ->where('product_id', $productId)
            ->where('status', DigitalCodeStatus::Available->value)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($variantId) {
            $codeQuery->where(function ($query) use ($variantId) {
                $query->where('product_variant_id', $variantId)
                    ->orWhereNull('product_variant_id');
            });
        }

        $code = $codeQuery->lockForUpdate()->first();

        if (! $code) {
            $productName = $orderItem->product?->getName('ar') ?? 'Digital Product';

            throw new RuntimeException(
                'No available digital code for product: ' . $productName
            );
        }

        $code->update([
            'status' => DigitalCodeStatus::Sold,
            'sold_at' => now(),
            'sold_to' => $orderItem->order?->customer?->user_id,
        ]);

        $orderItem->updateQuietly([
            'digital_code_id' => $code->id,
        ]);
    }
}