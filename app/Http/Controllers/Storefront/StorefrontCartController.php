<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\CartStatus;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class StorefrontCartController extends Controller
{
    public function add(Request $request): RedirectResponse
    {
        $locale = $this->resolveLocale($request);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);

        $product = Product::query()
            ->with(['currency'])
            ->where('is_active', true)
            ->findOrFail($validated['product_id']);

        $variant = null;

        if (! empty($validated['product_variant_id'])) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->findOrFail($validated['product_variant_id']);
        }

        $cart = $this->getOrCreateCart($product);

        $this->addItemToCart($cart, $product, $variant, $quantity, $locale);

        $this->recalculateCartTotals($cart);

        return back()
            ->with('success', __('storefront.cart.added_successfully'))
            ->with('cart_id', $cart->id);
    }

    private function getOrCreateCart(Product $product): Cart
    {
        $cartId = session('storefront_cart_id');

        if ($cartId) {
            $existingCart = Cart::query()
                ->where('id', $cartId)
                ->where('status', CartStatus::Active->value)
                ->where('is_active', true)
                ->first();

            if ($existingCart) {
                return $existingCart;
            }
        }

        $currency = $product->currency
            ?? Currency::query()->where('code', 'ILS')->first()
            ?? Currency::query()->first();

        $customer = $this->resolveCustomer();

        $cart = Cart::query()->create([
            'cart_number' => $this->generateCartNumber(),
            'customer_id' => $customer?->id,
            'user_id' => auth()->id(),
            'currency_id' => $currency?->id,
            'shipping_method_id' => null,
            'coupon_id' => null,
            'coupon_code' => null,
            'coupon_discount_type' => null,
            'coupon_discount_value' => 0,
            'status' => CartStatus::Active->value,
            'subtotal' => 0,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'grand_total' => 0,
            'customer_notes' => null,
            'internal_notes' => 'Created from public storefront.',
            'converted_at' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        session(['storefront_cart_id' => $cart->id]);

        return $cart;
    }

    private function addItemToCart(
        Cart $cart,
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        string $locale
    ): void {
        $unitPrice = $this->getUnitPrice($product, $variant);
        $itemType = $this->resolveItemType($product);

        $existingItem = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        if ($existingItem) {
            $newQuantity = (int) $existingItem->quantity + $quantity;
            $lineTotal = $unitPrice * $newQuantity;

            $existingItem->update([
                'quantity' => $newQuantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'product_name' => $variant?->name ?? $product->name,
                'sku' => $variant?->sku ?? $product->sku,
                'item_type' => $itemType,
                'discount_total' => 0,
                'tax_total' => 0,
                'options' => $variant?->option_values,
            ]);

            return;
        }

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'product_name' => $variant?->name ?? $product->name,
            'sku' => $variant?->sku ?? $product->sku,
            'item_type' => $itemType,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $unitPrice * $quantity,
            'discount_total' => 0,
            'tax_total' => 0,
            'options' => $variant?->option_values,
            'notes' => 'Added from storefront. Locale: ' . $locale,
        ]);
    }

    private function getUnitPrice(Product $product, ?ProductVariant $variant): float
    {
        if ($variant) {
            if (method_exists($variant, 'finalPrice')) {
                return (float) $variant->finalPrice();
            }

            if (isset($variant->sale_price) && (float) $variant->sale_price > 0) {
                return (float) $variant->sale_price;
            }

            if (isset($variant->price)) {
                return (float) $variant->price;
            }
        }

        if (method_exists($product, 'finalPrice')) {
            return (float) $product->finalPrice();
        }

        if (isset($product->sale_price) && (float) $product->sale_price > 0) {
            return (float) $product->sale_price;
        }

        return (float) $product->price;
    }

    private function recalculateCartTotals(Cart $cart): void
    {
        $cart->load('items');

        $subtotal = (float) $cart->items->sum(function ($item) {
            return (float) $item->line_total;
        });

        $discountTotal = (float) ($cart->discount_total ?? 0);
        $taxTotal = (float) ($cart->tax_total ?? 0);
        $shippingTotal = (float) ($cart->shipping_total ?? 0);

        $grandTotal = max($subtotal - $discountTotal + $taxTotal + $shippingTotal, 0);

        $cart->update([
            'subtotal' => $subtotal,
            'grand_total' => $grandTotal,
        ]);
    }

    private function resolveItemType(Product $product): string
    {
        $type = $product->product_type ?? null;

        if ($type instanceof \BackedEnum) {
            $type = $type->value;
        }

        return match ((string) $type) {
            'digital', 'digital_code' => 'digital_code',
            'service' => 'service',
            default => 'product',
        };
    }

    private function resolveCustomer(): ?Customer
    {
        if (! auth()->check()) {
            return null;
        }

        return Customer::query()
            ->where('user_id', auth()->id())
            ->first();
    }

    private function generateCartNumber(): string
    {
        do {
            $number = 'CART-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        } while (Cart::query()->where('cart_number', $number)->exists());

        return $number;
    }

    private function resolveLocale(Request $request): string
    {
        $allowedLocales = ['ar', 'he', 'en'];

        $locale = $request->input('lang')
            ?? $request->query('lang')
            ?? session('storefront_locale')
            ?? app()->getLocale()
            ?? 'ar';

        if (! in_array($locale, $allowedLocales, true)) {
            $locale = 'ar';
        }

        session(['storefront_locale' => $locale]);

        App::setLocale($locale);

        return $locale;
    }
}