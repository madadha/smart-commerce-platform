<?php

namespace Tests\Feature;

use App\Enums\ShipmentStatus;
use App\Mail\ShipmentStatusUpdatedMail;
use App\Models\Cart;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Services\Shipping\ShipmentService;
use App\Services\Shipping\ShippingQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ShippingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_quote_enforces_location_and_weight_and_calculates_server_price(): void
    {
        $currency = Currency::query()->firstOrCreate(['code' => 'ILS'], ['name' => ['en' => 'Shekel'], 'symbol' => '₪', 'exchange_rate' => 1, 'is_active' => true]);
        $country = Country::query()->forceCreate(['name' => ['en' => 'Israel'], 'code' => 'IL', 'currency_id' => $currency->id, 'is_active' => true]);
        $product = Product::query()->forceCreate([
            'name' => ['en' => 'Weighted product'], 'slug' => 'weighted-product', 'sku' => 'WEIGHT-1',
            'product_type' => 'physical', 'status' => 'active', 'currency_id' => $currency->id,
            'price' => 100, 'weight' => 2.5, 'stock_quantity' => 10, 'is_active' => true,
        ]);
        $method = ShippingMethod::query()->forceCreate([
            'name' => ['en' => 'Courier'], 'slug' => 'courier', 'type' => 'standard',
            'country_id' => $country->id, 'base_cost' => 10, 'per_kg_cost' => 4,
            'max_weight' => 6, 'allowed_cities' => ['jerusalem'], 'is_active' => true,
        ]);
        $cart = Cart::query()->forceCreate(['currency_id' => $currency->id, 'subtotal' => 200, 'is_active' => true]);
        $cart->items()->forceCreate([
            'product_id' => $product->id, 'product_name' => ['en' => 'Weighted product'],
            'item_type' => 'product', 'quantity' => 2, 'unit_price' => 100, 'line_total' => 200,
        ]);

        $quotes = app(ShippingQuoteService::class)->quoteCart($cart, $country->id, ' Jerusalem ');
        $this->assertCount(1, $quotes);
        $this->assertSame($method->id, $quotes->first()['method']->id);
        $this->assertSame(5.0, $quotes->first()['weight']);
        $this->assertSame(30.0, $quotes->first()['cost']);
        $this->assertCount(0, app(ShippingQuoteService::class)->quoteCart($cart, $country->id, 'Haifa'));
    }

    public function test_shipment_status_creates_customer_timeline_and_completes_order(): void
    {
        Mail::fake();
        $customer = Customer::query()->forceCreate([
            'first_name' => 'Shipping', 'last_name' => 'Customer',
            'email' => 'shipping@example.test', 'status' => 'active', 'is_active' => true,
        ]);
        $order = Order::query()->forceCreate([
            'order_number' => 'ORD-SHIPPING-1', 'status' => 'pending', 'payment_status' => 'paid',
            'customer_id' => $customer->id, 'locale' => 'en',
            'subtotal' => 100, 'shipping_total' => 20, 'grand_total' => 120,
            'shipping_weight' => 1.25, 'shipping_address' => ['city' => 'Jerusalem'], 'is_active' => true,
        ]);

        $shipment = app(ShipmentService::class)->createForOrder($order, ['tracking_number' => 'TRACK-123']);
        $this->assertSame(ShipmentStatus::Pending, $shipment->status);
        $this->assertCount(1, $shipment->events);

        app(ShipmentService::class)->transition($shipment, ShipmentStatus::Delivered, 'Received by customer', 'Jerusalem');

        $this->assertNotNull($shipment->fresh()->delivered_at);
        $this->assertSame('completed', $order->fresh()->status->value);
        $this->assertDatabaseHas('shipment_events', [
            'shipment_id' => $shipment->id, 'status' => 'delivered',
            'description' => 'Received by customer', 'location' => 'Jerusalem',
        ]);
        Mail::assertSent(ShipmentStatusUpdatedMail::class, function (ShipmentStatusUpdatedMail $mail): bool {
            return $mail->mailLocale === 'en'
                && $mail->shipment->tracking_number === 'TRACK-123';
        });
    }

    public function test_shipment_creation_uses_safe_weight_when_order_weight_is_missing(): void
    {
        $currency = Currency::query()->firstOrCreate(['code' => 'ILS'], ['name' => ['en' => 'Shekel'], 'symbol' => '₪', 'exchange_rate' => 1, 'is_active' => true]);
        $product = Product::query()->forceCreate([
            'name' => ['en' => 'Console'], 'slug' => 'console-'.uniqid(), 'sku' => 'CONSOLE-1',
            'product_type' => 'physical', 'status' => 'active', 'currency_id' => $currency->id,
            'price' => 100, 'weight' => 2.5, 'stock_quantity' => 10, 'is_active' => true,
        ]);
        $method = ShippingMethod::query()->forceCreate([
            'name' => ['en' => 'Standard'], 'slug' => 'standard-'.uniqid(), 'type' => 'standard',
            'base_cost' => 10, 'is_active' => true,
        ]);
        $order = Order::query()->forceCreate([
            'order_number' => 'ORD-WEIGHT-'.uniqid(), 'status' => 'pending', 'payment_status' => 'paid',
            'currency_id' => $currency->id, 'shipping_method_id' => $method->id,
            'subtotal' => 100, 'shipping_total' => 10, 'grand_total' => 110,
            'shipping_weight' => 0, 'shipping_address' => 'Basmat Tab\'un', 'is_active' => true,
        ]);
        $order->forceFill(['shipping_weight' => null]);
        $order->items()->forceCreate([
            'product_id' => $product->id, 'product_name' => ['en' => 'Console'],
            'item_type' => 'product', 'quantity' => 2, 'unit_price' => 100, 'line_total' => 200,
        ]);

        $shipment = app(ShipmentService::class)->createForOrder($order);

        $this->assertSame('5.000', $shipment->weight);
    }
}
