<?php

namespace App\Services\Checkout;

use App\Enums\CartStatus;
use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CartCheckoutService
{
    public function convertToOrder(
        Cart $cart,
        bool $createPayment = false,
        string $paymentMethod = 'cash',
        ?string $transactionId = null
    ): Order {
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

        return DB::transaction(function () use ($cart, $createPayment, $paymentMethod, $transactionId): Order {
            $cart->refresh();
            $cart->load(['items.product', 'items.productVariant', 'customer.country']);
            $cart->recalculateTotals();

            $order = $this->createOrderFromCart($cart);

            $this->createOrderItemsFromCart($cart, $order);

            $order->refresh();
            $order->load(['items.product', 'items.productVariant', 'customer']);

            $this->processInventoryAndDigitalCodes($order);

            $order->refresh();
            $order->recalculateTotals();

            if ($createPayment) {
                $this->createPaymentForOrder($order, $paymentMethod, $transactionId);
                $order->refresh();
            }

            $this->createInvoiceForOrder($order);

            $cart->update([
                'status' => CartStatus::Converted,
                'converted_at' => now(),
                'is_active' => false,
            ]);

            return $order->refresh();
        });
    }

    private function createOrderFromCart(Cart $cart): Order
    {
        return Order::query()->create([
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
    }

    private function createOrderItemsFromCart(Cart $cart, Order $order): void
    {
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
    }

    private function processInventoryAndDigitalCodes(Order $order): void
    {
        $inventoryService = app(InventoryService::class);

        foreach ($order->items as $orderItem) {
            $inventoryService->processOrderItem($orderItem);
        }
    }

    private function createPaymentForOrder(Order $order, string $paymentMethod, ?string $transactionId = null): Payment
    {
        return Payment::query()->create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'currency_id' => $order->currency_id,
            'payment_method' => $paymentMethod,
            'status' => PaymentTransactionStatus::Paid,
            'amount' => $order->grand_total,
            'refunded_amount' => 0,
            'transaction_id' => $transactionId ?? 'CHECKOUT-' . $order->order_number,
            'provider' => $paymentMethod,
            'provider_reference' => null,
            'provider_payload' => [
                'source' => 'checkout_finalization',
                'order_number' => $order->order_number,
            ],
            'paid_at' => now(),
            'internal_notes' => 'Payment created automatically during checkout finalization.',
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function createInvoiceForOrder(Order $order): Invoice
    {
        $order->refresh();
        $order->load(['items', 'customer', 'currency']);

        $invoice = Invoice::query()->create([
            'order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'currency_id' => $order->currency_id,
            'status' => $order->payment_status === PaymentStatus::Paid
                ? InvoiceStatus::Paid
                : InvoiceStatus::Issued,
            'discount_total' => $order->discount_total,
            'tax_total' => $order->tax_total,
            'shipping_total' => $order->shipping_total,
            'paid_total' => $order->paid_total,
            'billing_address' => $order->billing_address,
            'seller_details' => [
                'name' => 'Smart Commerce Platform',
                'email' => 'info@example.com',
                'phone' => '+972000000000',
                'tax_number' => '000000000',
            ],
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(14)->toDateString(),
            'paid_at' => $order->payment_status === PaymentStatus::Paid
                ? now()->toDateString()
                : null,
            'customer_notes' => $order->customer_notes,
            'internal_notes' => 'Invoice created automatically from checkout finalization.',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        foreach ($order->items as $orderItem) {
            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'order_item_id' => $orderItem->id,
                'product_id' => $orderItem->product_id,
                'product_variant_id' => $orderItem->product_variant_id,
                'item_name' => $orderItem->product_name,
                'sku' => $orderItem->sku,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'discount_total' => $orderItem->discount_total,
                'tax_total' => $orderItem->tax_total,
                'options' => $orderItem->options,
                'notes' => $orderItem->notes,
            ]);
        }

        $invoice->refresh();
        $invoice->recalculateTotals();

        return $invoice;
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