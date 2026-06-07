<?php

namespace App\Services\Checkout;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class CartCheckoutService
{
    public function __construct(
        private readonly CheckoutInventoryService $checkoutInventoryService
    ) {
    }

    public function convertCartToOrder(Cart $cart, array $data, ?int $userId = null): Order
    {
        return DB::transaction(function () use ($cart, $data, $userId) {
            $cart->load([
                'items.product',
                'items.productVariant',
                'currency',
            ]);

            if ($cart->items->isEmpty()) {
                throw new RuntimeException('Cart is empty.');
            }

            $this->validateCartBeforeCheckout($cart);

            $customer = $this->findOrCreateCustomer($data, $userId);

            $shippingTotal = $this->resolveShippingTotal($data['shipping_method_id'] ?? null);

            $subtotal = (float) $cart->items->sum(function ($item) {
                return (float) ($item->line_total ?? ((float) $item->unit_price * (int) $item->quantity));
            });

            $discountTotal = (float) ($cart->discount_total ?? 0);
            $taxTotal = (float) ($cart->tax_total ?? 0);
            $grandTotal = max($subtotal - $discountTotal + $taxTotal + $shippingTotal, 0);

            $cart->forceFill($this->filterColumns('carts', [
                'customer_id' => $customer?->id,
                'shipping_method_id' => $data['shipping_method_id'] ?? null,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'shipping_total' => $shippingTotal,
                'grand_total' => $grandTotal,
                'customer_notes' => $data['customer_notes'] ?? null,
            ]))->save();

            $order = $this->createModel(Order::class, 'orders', [
                'order_number' => $this->generateOrderNumber(),
                'customer_id' => $customer?->id,
                'user_id' => $userId,
                'currency_id' => $cart->currency_id,
                'shipping_method_id' => $data['shipping_method_id'] ?? null,

                'coupon_id' => $cart->coupon_id ?? null,
                'coupon_code' => $cart->coupon_code ?? null,
                'coupon_discount_type' => $cart->coupon_discount_type ?? null,
                'coupon_discount_value' => $cart->coupon_discount_value ?? 0,

                'status' => 'pending',
                'payment_status' => 'unpaid',

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
                'grand_total' => $grandTotal,

                'customer_notes' => $data['customer_notes'] ?? null,
                'internal_notes' => 'Created from storefront checkout. Cart: ' . $cart->cart_number,

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

            $this->createPendingPaymentIfPossible($order, $customer, $cart, $data, $grandTotal);

            $cart->forceFill($this->filterColumns('carts', [
                'status' => CartStatus::Converted->value,
                'converted_at' => now(),
                'is_active' => false,
            ]))->save();

            return $order->refresh();
        });
    }

    private function validateCartBeforeCheckout(Cart $cart): void
    {
        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;

            if (! $product) {
                throw new RuntimeException('Product not found in cart item.');
            }

            $quantity = max((int) $cartItem->quantity, 1);
            $productType = $this->resolveProductType($product);

            if ($productType === 'digital' || $productType === 'digital_code' || $cartItem->item_type === 'digital_code') {
                $this->validateDigitalCodeAvailability($product->id, $quantity);
                continue;
            }

            if ($cartItem->productVariant) {
                $this->validateVariantStock($cartItem->productVariant, $quantity);
                continue;
            }

            $this->validateProductStock($product, $quantity);
        }
    }

    private function validateProductStock($product, int $quantity): void
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

        if ((int) ($product->stock_quantity ?? 0) < $quantity) {
            throw new RuntimeException('Insufficient stock for product: ' . ($product->sku ?? $product->id));
        }
    }

    private function validateVariantStock($variant, int $quantity): void
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

        if ((int) ($variant->stock_quantity ?? 0) < $quantity) {
            throw new RuntimeException('Insufficient stock for variant: ' . ($variant->sku ?? $variant->id));
        }
    }

    private function validateDigitalCodeAvailability(int $productId, int $quantity): void
    {
        if (! Schema::hasTable('product_digital_codes')) {
            throw new RuntimeException('Digital codes table does not exist.');
        }

        $availableCount = DB::table('product_digital_codes')
            ->where('product_id', $productId)
            ->where(function ($query) {
                $query->where('status', 'available')
                    ->orWhereNull('status');
            })
            ->count();

        if ($availableCount < $quantity) {
            throw new RuntimeException('Not enough digital codes available.');
        }
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

    private function resolveShippingTotal(?int $shippingMethodId): float
    {
        if (! $shippingMethodId || ! Schema::hasTable('shipping_methods')) {
            return 0;
        }

        $shippingMethod = ShippingMethod::query()->find($shippingMethodId);

        if (! $shippingMethod) {
            return 0;
        }

        foreach (['price', 'cost', 'amount', 'shipping_cost', 'base_cost'] as $column) {
            if (Schema::hasColumn('shipping_methods', $column) && $shippingMethod->{$column} !== null) {
                return (float) $shippingMethod->{$column};
            }
        }

        return 0;
    }

    private function createPendingPaymentIfPossible(
        Order $order,
        ?Customer $customer,
        Cart $cart,
        array $data,
        float $amount
    ): void {
        if (! class_exists(Payment::class) || ! Schema::hasTable('payments')) {
            return;
        }

        $this->createModel(Payment::class, 'payments', [
            'payment_number' => $this->generatePaymentNumber(),
            'order_id' => $order->id,
            'customer_id' => $customer?->id,
            'currency_id' => $cart->currency_id,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'method' => $data['payment_method'] ?? 'cash',
            'status' => 'pending',
            'amount' => $amount,
            'paid_at' => null,
            'notes' => 'Created automatically from storefront checkout.',
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function createModel(string $modelClass, string $table, array $attributes): Model
    {
        /** @var Model $model */
        $model = new $modelClass();

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

    private function resolveProductType($product): string
    {
        $type = $product->product_type ?? null;

        if ($type instanceof \BackedEnum) {
            return (string) $type->value;
        }

        return (string) $type;
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }

    private function generatePaymentNumber(): string
    {
        if (! Schema::hasTable('payments')) {
            return 'PAY-' . strtoupper(Str::random(8));
        }

        do {
            $number = 'PAY-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Payment::query()->where('payment_number', $number)->exists());

        return $number;
    }
}