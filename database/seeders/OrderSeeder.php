<?php

namespace Database\Seeders;

use App\Enums\DigitalCodeStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductDigitalCode;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::query()->first();
        $currency = Currency::query()->where('code', 'ILS')->first();

        $iphone = Product::query()
            ->where('slug', 'iphone-16-pro-max')
            ->first();

        $psCard = Product::query()
            ->where('slug', 'playstation-store-card-50-us')
            ->first();

        if (! $customer || ! $currency || ! $iphone || ! $psCard) {
            return;
        }

        $order = Order::query()->updateOrCreate(
            [
                'order_number' => 'ORD-DEMO-00001',
            ],
            [
                'customer_id' => $customer->id,
                'currency_id' => $currency->id,
                'status' => OrderStatus::Processing,
                'payment_status' => PaymentStatus::Paid,
                'payment_method' => 'credit_card',
                'shipping_method' => 'home_delivery',
                'discount_total' => 100,
                'tax_total' => 0,
                'shipping_total' => 30,
                'paid_total' => 4889,
                'billing_address' => [
                    'name' => $customer->getDisplayName(),
                    'phone' => $customer->phone,
                    'address' => $customer->getFullAddress(),
                ],
                'shipping_address' => [
                    'name' => $customer->getDisplayName(),
                    'phone' => $customer->phone,
                    'address' => $customer->getFullAddress(),
                ],
                'customer_notes' => 'Demo order.',
                'internal_notes' => 'Created by OrderSeeder.',
                'ordered_at' => now(),
                'paid_at' => now(),
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        OrderItem::query()->updateOrCreate(
            [
                'order_id' => $order->id,
                'product_id' => $iphone->id,
            ],
            [
                'product_variant_id' => null,
                'product_name' => $iphone->name,
                'sku' => $iphone->sku,
                'item_type' => 'product',
                'quantity' => 1,
                'unit_price' => 4699,
                'discount_total' => 0,
                'tax_total' => 0,
                'options' => null,
                'notes' => 'Demo physical item.',
            ]
        );

        $digitalCode = ProductDigitalCode::query()
            ->where('product_id', $psCard->id)
            ->where('status', DigitalCodeStatus::Available->value)
            ->first();

        OrderItem::query()->updateOrCreate(
            [
                'order_id' => $order->id,
                'product_id' => $psCard->id,
            ],
            [
                'product_variant_id' => null,
                'product_name' => $psCard->name,
                'sku' => $psCard->sku,
                'item_type' => 'digital_code',
                'quantity' => 1,
                'unit_price' => 190,
                'discount_total' => 0,
                'tax_total' => 0,
                'digital_code_id' => $digitalCode?->id,
                'options' => null,
                'notes' => 'Demo digital code item.',
            ]
        );

        if ($digitalCode) {
            $digitalCode->update([
                'status' => DigitalCodeStatus::Sold,
                'sold_at' => now(),
                'sold_to' => $order->customer?->user_id,
            ]);
        }

        $order->refresh();
        $order->recalculateTotals();
    }
}