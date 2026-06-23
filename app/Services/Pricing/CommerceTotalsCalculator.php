<?php

namespace App\Services\Pricing;

use App\Models\Coupon;
use App\Models\ShippingMethod;

class CommerceTotalsCalculator
{
    public function calculate(
        float $subtotal,
        float $taxTotal = 0,
        ?float $taxRate = null,
        ?Coupon $coupon = null,
        ?ShippingMethod $shippingMethod = null,
        ?float $shippingTotalOverride = null,
    ): array {
        $subtotal = $this->money($subtotal);
        $taxTotal = $taxRate !== null
            ? $this->money(max($subtotal * max($taxRate, 0) / 100, 0))
            : $this->money(max($taxTotal, 0));
        $shippingTotal = $this->money($shippingTotalOverride ?? $shippingMethod?->calculateCost($subtotal) ?? 0);
        $discountTotal = $this->money($coupon?->calculateDiscount($subtotal, $shippingTotal) ?? 0);
        $grandTotal = $this->money(max($subtotal - $discountTotal + $taxTotal + $shippingTotal, 0));

        return compact(
            'subtotal',
            'discountTotal',
            'taxTotal',
            'shippingTotal',
            'grandTotal'
        );
    }

    private function money(float $amount): float
    {
        return round($amount, 2);
    }
}
