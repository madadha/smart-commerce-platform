<?php

namespace App\Services\Shipping;

use App\Models\Cart;
use App\Models\ShippingMethod;
use Illuminate\Support\Collection;
use RuntimeException;

class ShippingQuoteService
{
    public function quoteCart(Cart $cart, ?int $countryId, string $city): Collection
    {
        $cart->loadMissing(['items.product', 'items.productVariant']);
        $subtotal = (float) $cart->items->sum(fn ($item) => (float) ($item->line_total ?? 0));
        $weight = $this->cartWeight($cart);

        return ShippingMethod::query()->active()->ordered()->get()
            ->filter(fn (ShippingMethod $method) => $this->isEligible($method, $subtotal, $weight, $countryId, $city))
            ->map(fn (ShippingMethod $method) => [
                'method' => $method,
                'cost' => $method->calculateCost($subtotal, $weight),
                'weight' => $weight,
                'min_delivery_days' => $method->min_delivery_days,
                'max_delivery_days' => $method->max_delivery_days,
            ])->values();
    }

    public function requireQuote(Cart $cart, int $methodId, ?int $countryId, string $city): array
    {
        $quote = $this->quoteCart($cart, $countryId, $city)
            ->first(fn (array $quote) => $quote['method']->id === $methodId);

        if (! $quote) {
            throw new RuntimeException(__('storefront.checkout.shipping_unavailable'));
        }

        return $quote;
    }

    public function cartWeight(Cart $cart): float
    {
        return round((float) $cart->items->sum(function ($item) {
            if (in_array(strtolower((string) $item->item_type), ['digital', 'service'], true)) {
                return 0;
            }

            $weight = $item->productVariant?->weight ?? $item->product?->weight ?? 0;

            return (float) $weight * max((int) $item->quantity, 1);
        }), 3);
    }

    private function isEligible(ShippingMethod $method, float $subtotal, float $weight, ?int $countryId, string $city): bool
    {
        if ($method->country_id && (int) $method->country_id !== (int) $countryId) {
            return false;
        }
        if ($method->min_order_total !== null && $subtotal < (float) $method->min_order_total) {
            return false;
        }
        if ($method->max_order_total !== null && $subtotal > (float) $method->max_order_total) {
            return false;
        }
        if ($method->min_weight !== null && $weight < (float) $method->min_weight) {
            return false;
        }
        if ($method->max_weight !== null && $weight > (float) $method->max_weight) {
            return false;
        }

        $city = $this->normalizeCity($city);
        $allowed = $this->normalizeCities($method->allowed_cities);
        $excluded = $this->normalizeCities($method->excluded_cities);

        return ! in_array($city, $excluded, true) && ($allowed === [] || in_array($city, $allowed, true));
    }

    private function normalizeCities(?array $cities): array
    {
        return collect($cities ?? [])->flatMap(fn ($value, $key) => is_numeric($key) ? [$value] : [$key, $value])
            ->map(fn ($city) => $this->normalizeCity((string) $city))->filter()->unique()->values()->all();
    }

    private function normalizeCity(string $city): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $city) ?? $city));
    }
}
