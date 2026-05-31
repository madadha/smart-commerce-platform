<?php

namespace App\Services\Checkout;

use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CartCheckoutService
{
    public function convertToOrder(Cart $cart): Order
    {
        if ($cart->isConverted()) {
            $existingOrder = Order::query()
                ->where('internal_notes', 'like', '%Converted from cart: ' . $cart->cart_number . '%')
                ->first();

            if ($existingOrder) {
                return $existingOrder;
            }

            throw new RuntimeException('This cart is already converted.');
        }

        if (! $cart->items()->exists()) {
            throw new RuntimeException('Cannot convert an empty cart.');
        }

        return DB::transaction(function () use ($cart): Order {
            $cart->refresh();
            $cart->recalculateTotals();

            $order = Order::query()->create([
                'customer_id' => $cart->customer_id,
                'user_id' => $cart->user_id,
                'currency_id' => $cart->currency_id,
                'shipping_method_id' => $cart->shipping_method_id,
                'coupon_id' => $cart->coupon_id,
                'coupon_code' => $cart->coupon_code,
                'coupon_discount_type' => $cart->coupon_discount_type,
                'coupon_discount_value' => $cart->coupon_discount_value,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'payment_method' => null,
                'subtotal' => $cart->subtotal,
                'discount_total' => $cart->discount_total,
                'tax_total' => $cart->tax_total,
                'shipping_total' => $cart->shipping_total,
                'grand_total' => $cart->grand_total,
                'paid_total' => 0,
                'billing_address' => $this->buildAddressFromCart($cart),
                'shipping_address' => $this->buildAddressFromCart($cart),
                'customer_notes' => $cart->customer_notes,
                'internal_notes' => trim(
                    ($cart->internal_notes ? $cart->internal_notes . PHP_EOL : '') .
                    'Converted from cart: ' . $cart->cart_number
                ),
                'ordered_at' => now(),
                'is_active' => true,
                'sort_order' => 0,
            ]);

            foreach ($cart->items as $cartItem) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'product_name' => $cartItem->product_name,
                    'sku' => $cartItem->sku,
                    'item_type' => $cartItem->item_type,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'discount_total' => $cartItem->discount_total,
                    'tax_total' => $cartItem->tax_total,
                    'options' => $cartItem->options,
                    'digital_code_id' => null,
                    'notes' => $cartItem->notes,
                ]);
            }

            $cart->update([
                'status' => CartStatus::Converted,
                'converted_at' => now(),
                'is_active' => false,
            ]);

            $order->refresh();
            $order->recalculateTotals();

            return $order;
        });
    }

    private function buildAddressFromCart(Cart $cart): ?array
    {
        $customer = $cart->customer;

        if (! $customer) {
            return null;
        }

        return [
            'name' => $customer->getDisplayName(),
            'email' => $customer->email,
            'phone' => $customer->phone,
            'whatsapp' => $customer->whatsapp,
            'country' => $customer->country?->getName('ar'),
            'city' => $customer->city,
            'area' => $customer->area,
            'street' => $customer->street,
            'building' => $customer->building,
            'apartment' => $customer->apartment,
            'postal_code' => $customer->postal_code,
            'address' => $customer->getFullAddress(),
        ];
    }
}