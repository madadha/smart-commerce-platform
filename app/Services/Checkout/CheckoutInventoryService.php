<?php

namespace App\Services\Checkout;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductDigitalCode;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CheckoutInventoryService
{
    public function handleOrderInventory(Order $order): void
    {
        $order->load([
            'items.product',
            'items.productVariant',
        ]);

        foreach ($order->items as $orderItem) {
            $product = $orderItem->product;

            if (! $product) {
                continue;
            }

            $quantity = max((int) $orderItem->quantity, 1);
            $productType = $this->resolveProductType($product);

            if ($productType === 'digital' || $productType === 'digital_code' || $orderItem->item_type === 'digital_code') {
                $this->assignDigitalCodes($orderItem, $product, $quantity);
                continue;
            }

            $this->deductPhysicalStock($orderItem, $product, $orderItem->productVariant, $quantity);
        }
    }

    private function deductPhysicalStock(
        OrderItem $orderItem,
        Product $product,
        ?ProductVariant $variant,
        int $quantity
    ): void {
        if ($variant) {
            $this->deductVariantStock($variant, $quantity, $orderItem);
            return;
        }

        $this->deductProductStock($product, $quantity, $orderItem);
    }

    private function deductProductStock(Product $product, int $quantity, OrderItem $orderItem): void
    {
        if (! Schema::hasColumn('products', 'stock_quantity')) {
            return;
        }

        $trackStock = Schema::hasColumn('products', 'track_stock')
            ? (bool) $product->track_stock
            : true;

        if (! $trackStock) {
            return;
        }

        $currentStock = (int) ($product->stock_quantity ?? 0);

        if ($currentStock < $quantity) {
            throw new RuntimeException(
                'Insufficient stock for product: ' . ($product->sku ?? $product->id)
            );
        }

        $product->forceFill([
            'stock_quantity' => $currentStock - $quantity,
        ])->save();

        $this->appendOrderItemNote(
            $orderItem,
            'Stock deducted from product. Quantity: ' . $quantity
        );
    }

    private function deductVariantStock(ProductVariant $variant, int $quantity, OrderItem $orderItem): void
    {
        if (! Schema::hasColumn('product_variants', 'stock_quantity')) {
            return;
        }

        $trackStock = Schema::hasColumn('product_variants', 'track_stock')
            ? (bool) $variant->track_stock
            : true;

        if (! $trackStock) {
            return;
        }

        $currentStock = (int) ($variant->stock_quantity ?? 0);

        if ($currentStock < $quantity) {
            throw new RuntimeException(
                'Insufficient stock for variant: ' . ($variant->sku ?? $variant->id)
            );
        }

        $variant->forceFill([
            'stock_quantity' => $currentStock - $quantity,
        ])->save();

        $this->appendOrderItemNote(
            $orderItem,
            'Stock deducted from variant. Quantity: ' . $quantity
        );
    }

    private function assignDigitalCodes(OrderItem $orderItem, Product $product, int $quantity): void
    {
        if (! class_exists(ProductDigitalCode::class) || ! Schema::hasTable('product_digital_codes')) {
            throw new RuntimeException('Digital codes module is not available.');
        }

        $availableCodes = ProductDigitalCode::query()
            ->where('product_id', $product->id)
            ->where(function ($query) {
                $query->where('status', 'available')
                    ->orWhereNull('status');
            })
            ->orderBy('id')
            ->lockForUpdate()
            ->limit($quantity)
            ->get();

        if ($availableCodes->count() < $quantity) {
            throw new RuntimeException(
                'Not enough digital codes available for product: ' . ($product->sku ?? $product->id)
            );
        }

        foreach ($availableCodes as $code) {
            $updateData = [];

            if (Schema::hasColumn('product_digital_codes', 'status')) {
                $updateData['status'] = 'sold';
            }

            if (Schema::hasColumn('product_digital_codes', 'order_id')) {
                $updateData['order_id'] = $orderItem->order_id;
            }

            if (Schema::hasColumn('product_digital_codes', 'order_item_id')) {
                $updateData['order_item_id'] = $orderItem->id;
            }

            if (Schema::hasColumn('product_digital_codes', 'sold_at')) {
                $updateData['sold_at'] = now();
            }

            if (Schema::hasColumn('product_digital_codes', 'reserved_at')) {
                $updateData['reserved_at'] = null;
            }

            if (! empty($updateData)) {
                $code->forceFill($updateData)->save();
            }
        }

        $this->appendOrderItemNote(
            $orderItem,
            'Digital codes assigned/sold. Quantity: ' . $quantity
        );
    }

    private function appendOrderItemNote(OrderItem $orderItem, string $note): void
    {
        $oldNotes = trim((string) ($orderItem->notes ?? ''));

        $newNotes = $oldNotes === ''
            ? $note
            : $oldNotes . PHP_EOL . $note;

        $orderItem->forceFill([
            'notes' => $newNotes,
        ])->save();
    }

    private function resolveProductType(Product $product): string
    {
        $type = $product->product_type ?? null;

        if ($type instanceof \BackedEnum) {
            return (string) $type->value;
        }

        return (string) $type;
    }
}