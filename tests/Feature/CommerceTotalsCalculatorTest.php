<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ShippingMethod;
use App\Services\Pricing\CommerceTotalsCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceTotalsCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_shipping_coupon_tax_and_grand_total_in_one_place(): void
    {
        $coupon = Coupon::query()->forceCreate([
            'code' => 'SAVE10',
            'name' => ['en' => 'Save 10%'],
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
        ]);
        $shipping = ShippingMethod::query()->forceCreate([
            'name' => ['en' => 'Delivery'],
            'slug' => 'delivery-'.uniqid(),
            'type' => 'home_delivery',
            'base_cost' => 20,
            'is_active' => true,
        ]);

        $totals = app(CommerceTotalsCalculator::class)->calculate(
            subtotal: 100,
            taxTotal: 5,
            coupon: $coupon,
            shippingMethod: $shipping,
        );

        $this->assertSame(100.0, $totals['subtotal']);
        $this->assertSame(10.0, $totals['discountTotal']);
        $this->assertSame(5.0, $totals['taxTotal']);
        $this->assertSame(20.0, $totals['shippingTotal']);
        $this->assertSame(115.0, $totals['grandTotal']);
    }

    public function test_free_shipping_threshold_is_applied_before_coupon_calculation(): void
    {
        $shipping = ShippingMethod::query()->forceCreate([
            'name' => ['en' => 'Free over 100'],
            'slug' => 'free-over-100-'.uniqid(),
            'type' => 'home_delivery',
            'base_cost' => 20,
            'free_shipping_min_total' => 100,
            'is_active' => true,
        ]);

        $totals = app(CommerceTotalsCalculator::class)->calculate(
            subtotal: 100,
            shippingMethod: $shipping,
        );

        $this->assertSame(0.0, $totals['shippingTotal']);
        $this->assertSame(100.0, $totals['grandTotal']);
    }

    public function test_country_tax_rate_is_calculated_from_the_subtotal(): void
    {
        $currency = Currency::query()->firstOrCreate([
            'code' => 'ILS',
        ], [
            'name' => ['en' => 'Israeli Shekel'],
            'symbol' => 'ILS',
            'exchange_rate' => 1,
            'is_active' => true,
        ]);

        Country::query()->forceCreate([
            'name' => ['en' => 'Israel'],
            'code' => 'IL',
            'currency_id' => $currency->id,
            'tax_rate' => 18,
            'is_active' => true,
        ]);

        $totals = app(CommerceTotalsCalculator::class)->calculate(
            subtotal: 200,
            taxRate: 18,
        );

        $this->assertSame(36.0, $totals['taxTotal']);
        $this->assertSame(236.0, $totals['grandTotal']);
    }
}
