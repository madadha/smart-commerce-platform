<?php

namespace App\Services\Checkout;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Payments\PaymentService;
use App\Services\Pricing\CommerceTotalsCalculator;
use App\Services\Shipping\ShipmentService;
use App\Services\Shipping\ShippingQuoteService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CartCheckoutService
{
    public function __construct(
        private readonly CheckoutInventoryService $checkoutInventoryService,
        private readonly CommerceTotalsCalculator $totalsCalculator,
        private readonly PaymentService $paymentService,
        private readonly ShippingQuoteService $shippingQuoteService,
        private readonly ShipmentService $shipmentService,
    ) {}

    public function convertCartToOrder(Cart $cart, array $data, ?int $userId = null): Order
    {
        $order = DB::transaction(function () use ($cart, $data, $userId) {
            $cart->load([
                'items.product',
                'items.productVariant',
                'currency',
            ]);

            if ($cart->items->isEmpty()) {
                throw new RuntimeException('Cart is empty.');
            }

            $customer = $this->findOrCreateCustomer($data, $userId);

            $subtotal = (float) $cart->items->sum(function ($item) {
                return (float) ($item->line_total ?? ((float) $item->unit_price * (int) $item->quantity));
            });
            $shippingQuote = isset($data['shipping_method_id'])
                ? $this->shippingQuoteService->requireQuote($cart, (int) $data['shipping_method_id'], $data['country_id'] ?? null, (string) ($data['city'] ?? ''))
                : null;
            $shippingMethod = $shippingQuote['method'] ?? null;
            $country = $this->resolveCountry($data['country_id'] ?? null);
            $totals = $this->totalsCalculator->calculate(
                subtotal: $subtotal,
                taxTotal: (float) ($cart->tax_total ?? 0),
                taxRate: $this->resolveTaxRate($country),
                coupon: $cart->coupon,
                shippingMethod: $shippingMethod,
                shippingTotalOverride: $shippingQuote['cost'] ?? 0,
            );
            $discountTotal = $totals['discountTotal'];
            $taxTotal = $totals['taxTotal'];
            $shippingTotal = $totals['shippingTotal'];
            $grandTotal = $totals['grandTotal'];

            $cart->forceFill($this->filterColumns('carts', [
                'customer_id' => $customer?->id,
                'shipping_method_id' => $data['shipping_method_id'] ?? null,
                'shipping_country_id' => $data['country_id'] ?? null,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'shipping_total' => $shippingTotal,
                'shipping_weight' => $shippingQuote['weight'] ?? 0,
                'shipping_min_delivery_days' => $shippingQuote['min_delivery_days'] ?? null,
                'shipping_max_delivery_days' => $shippingQuote['max_delivery_days'] ?? null,
                'grand_total' => $grandTotal,
                'customer_notes' => $data['customer_notes'] ?? null,
            ]))->save();

            $order = $this->createModel(Order::class, 'orders', [
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer?->id,
                'user_id' => $userId,
                'currency_id' => $cart->currency_id,
                'shipping_method_id' => $data['shipping_method_id'] ?? null,
                'shipping_country_id' => $data['country_id'] ?? null,

                'coupon_id' => $cart->coupon_id ?? null,
                'coupon_code' => $cart->coupon_code ?? null,
                'coupon_discount_type' => $cart->coupon_discount_type ?? null,
                'coupon_discount_value' => $cart->coupon_discount_value ?? 0,

                'status' => 'pending',
                'payment_status' => 'unpaid',
                'locale' => in_array(($data['lang'] ?? 'ar'), ['ar', 'he', 'en'], true) ? ($data['lang'] ?? 'ar') : 'ar',

                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,

                'shipping_city' => $data['city'] ?? null,
                'shipping_address' => $data['address'] ?? null,
                'billing_city' => $data['city'] ?? null,
                'billing_address' => $data['address'] ?? null,

                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'shipping_total' => $shippingTotal,
                'shipping_weight' => $shippingQuote['weight'] ?? 0,
                'shipping_min_delivery_days' => $shippingQuote['min_delivery_days'] ?? null,
                'shipping_max_delivery_days' => $shippingQuote['max_delivery_days'] ?? null,
                'grand_total' => $grandTotal,

                'customer_notes' => $data['customer_notes'] ?? null,
                'internal_notes' => 'Created from storefront checkout. Cart: '.$cart->cart_number,

                'is_active' => true,
                'sort_order' => 0,
            ]);

            foreach ($cart->items as $cartItem) {
                $unitPrice = (float) $cartItem->unit_price;
                $quantity = (int) $cartItem->quantity;
                $lineTotal = (float) ($cartItem->line_total ?? ($unitPrice * $quantity));

                $this->createModel(OrderItem::class, 'order_items', [
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'product_name' => $cartItem->product_name,
                    'sku' => $cartItem->sku,
                    'item_type' => $cartItem->item_type,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'discount_total' => (float) ($cartItem->discount_total ?? 0),
                    'tax_total' => (float) ($cartItem->tax_total ?? 0),
                    'options' => $this->normalizeArray($cartItem->options),
                    'notes' => $cartItem->notes,
                    'sort_order' => $cartItem->sort_order ?? 0,
                ]);
            }

            $order->refresh();
            $order->load([
                'items.product',
                'items.productVariant',
            ]);

            $this->checkoutInventoryService->handleOrderInventory($order);

            if ($shippingMethod && $order->items->contains(fn (OrderItem $item): bool => ! in_array(strtolower((string) $item->item_type), ['digital', 'digital_code', 'service'], true))) {
                $this->shipmentService->createForOrder($order);
            }

            $cart->forceFill($this->filterColumns('carts', [
                'status' => CartStatus::Converted->value,
                'converted_at' => now(),
                'is_active' => false,
            ]))->save();

            return $order->refresh();
        });

        $paymentMethod = (string) ($data['payment_method'] ?? 'cash');
        $this->paymentService->createAttempt(
            order: $order,
            method: $paymentMethod,
            idempotencyKey: "checkout:{$order->id}:{$paymentMethod}",
            context: ['source' => 'storefront_checkout'],
        );

        return $order->fresh();
    }

    private function findOrCreateCustomer(array $data, ?int $userId = null): ?Customer
    {
        if (! Schema::hasTable('customers')) {
            return null;
        }

        $email = trim((string) ($data['customer_email'] ?? ''));
        $phone = trim((string) ($data['customer_phone'] ?? ''));
        $fullName = trim((string) ($data['customer_name'] ?? ''));

        $existingCustomer = Customer::query()
            ->when($email !== '', function ($query) use ($email) {
                $query->where('email', $email);
            })
            ->when($email === '' && $phone !== '', function ($query) use ($phone) {
                $query->where('phone', $phone);
            })
            ->first();

        if ($existingCustomer) {
            $existingCustomer->forceFill($this->filterColumns('customers', [
                'user_id' => $existingCustomer->user_id ?? $userId,
                'name' => $fullName,
                'full_name' => $fullName,
                'email' => $email ?: $existingCustomer->email,
                'phone' => $phone ?: $existingCustomer->phone,
                'mobile' => $phone ?: ($existingCustomer->mobile ?? null),
                'city' => $data['city'] ?? ($existingCustomer->city ?? null),
                'address' => $data['address'] ?? ($existingCustomer->address ?? null),
            ]))->save();

            return $existingCustomer;
        }

        [$firstName, $lastName] = $this->splitName($fullName);

        return $this->createModel(Customer::class, 'customers', [
            'user_id' => $userId,
            'type' => 'individual',
            'status' => 'active',
            'name' => $fullName,
            'full_name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'company_name' => null,
            'email' => $email ?: null,
            'phone' => $phone ?: null,
            'mobile' => $phone ?: null,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'country' => 'Israel',
            'notes' => 'Created from storefront checkout.',
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function splitName(string $fullName): array
    {
        $fullName = trim($fullName);

        if ($fullName === '') {
            return ['Customer', ''];
        }

        $parts = preg_split('/\s+/', $fullName);

        $firstName = $parts[0] ?? $fullName;
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';

        return [$firstName, $lastName];
    }

    private function createModel(string $modelClass, string $table, array $attributes): Model
    {
        /** @var Model $model */
        $model = new $modelClass;

        $model->forceFill($this->filterColumns($table, $attributes));
        $model->save();

        return $model;
    }

    private function filterColumns(string $table, array $attributes): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        return collect($attributes)
            ->filter(function ($value, string $column) use ($table) {
                return Schema::hasColumn($table, $column);
            })
            ->toArray();
    }

    private function normalizeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }

    private function resolveCountry(?int $countryId): ?Country
    {
        if (! $countryId) {
            return null;
        }

        return Country::query()->find($countryId);
    }

    private function resolveTaxRate(?Country $country): ?float
    {
        if (! $country || $country->tax_rate === null) {
            return null;
        }

        return (float) $country->tax_rate;
    }
}
