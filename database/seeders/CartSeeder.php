<?php

namespace Database\Seeders;

use App\Enums\CartStatus;
use App\Enums\DigitalCodeStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductDigitalCode;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::query()->first();
        $currency = Currency::query()->where('code', 'ILS')->first();
        $shipping = ShippingMethod::query()->where('slug', 'home-delivery')->first();
        $coupon = Coupon::query()->where('code', 'WELCOME10')->first();

        $iphone = Product::query()
            ->where('slug', 'iphone-16-pro-max')
            ->first();

        $variant = ProductVariant::query()
            ->where('sku', 'IPHONE-16-PM-BLK-256')
            ->first();

        $psCard = Product::query()
            ->where('slug', 'playstation-store-card-50-us')
            ->first();

        if (! $customer || ! $currency || ! $iphone || ! $psCard) {
            return;
        }

        if ($variant && $variant->stock_quantity < 10) {
            $variant->update([
                'track_stock' => true,
                'stock_quantity' => 10,
            ]);
        }

        ProductDigitalCode::query()->updateOrCreate(
            ['code' => 'PSN-US-50-CHECKOUT-TEST-0001'],
            [
                'product_id' => $psCard->id,
                'product_variant_id' => null,
                'status' => DigitalCodeStatus::Available,
                'source' => 'manual',
                'expires_at' => now()->addYear(),
                'internal_notes' => 'Checkout test digital code.',
                'is_active' => true,
                'sort_order' => 100,
            ]
        );

        $cart = Cart::query()->updateOrCreate(
            [
                'cart_number' => 'CART-DEMO-00001',
            ],
            [
                'customer_id' => $customer->id,
                'currency_id' => $currency->id,
                'shipping_method_id' => $shipping?->id,
                'coupon_id' => $coupon?->id,
                'status' => CartStatus::Active,
                'customer_notes' => 'Demo cart.',
                'internal_notes' => 'Created by CartSeeder.',
                'converted_at' => null,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        CartItem::query()->updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $iphone->id,
                'product_variant_id' => $variant?->id,
            ],
            [
                'product_name' => $variant?->name ?? $iphone->name,
                'sku' => $variant?->sku ?? $iphone->sku,
                'item_type' => 'product',
                'quantity' => 1,
                'unit_price' => $variant?->finalPrice() ?? $iphone->finalPrice(),
                'discount_total' => 0,
                'tax_total' => 0,
                'options' => $variant?->option_values,
                'notes' => 'Demo cart physical product.',
            ]
        );

        CartItem::query()->updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $psCard->id,
                'product_variant_id' => null,
            ],
            [
                'product_name' => $psCard->name,
                'sku' => $psCard->sku,
                'item_type' => 'digital_code',
                'quantity' => 1,
                'unit_price' => $psCard->finalPrice(),
                'discount_total' => 0,
                'tax_total' => 0,
                'options' => null,
                'notes' => 'Demo cart digital product.',
            ]
        );

        $cart->refresh();
        $cart->recalculateTotals();
    }
}