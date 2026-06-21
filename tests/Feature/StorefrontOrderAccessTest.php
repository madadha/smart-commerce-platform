<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class StorefrontOrderAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_details_require_a_valid_signed_url(): void
    {
        $order = $this->createOrder();

        $this->get(route('storefront.orders.show', $order))->assertForbidden();

        $this->get(URL::signedRoute('storefront.orders.show', [
            'order' => $order->id,
            'lang' => 'en',
        ]))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_tampering_with_the_locale_invalidates_the_signed_order_url(): void
    {
        $order = $this->createOrder();
        $signedUrl = URL::signedRoute('storefront.orders.show', [
            'order' => $order->id,
            'lang' => 'en',
        ]);

        $this->get(str_replace('lang=en', 'lang=ar', $signedUrl))->assertForbidden();
    }

    public function test_signed_invoice_route_returns_a_pdf(): void
    {
        $order = $this->createOrder();

        $response = $this->get(URL::signedRoute('storefront.orders.invoice', [
            'order' => $order->id,
            'lang' => 'en',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    private function createOrder(): Order
    {
        return Order::query()->forceCreate([
            'order_number' => 'ORD-ACCESS-'.uniqid(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100,
            'grand_total' => 100,
            'is_active' => true,
        ]);
    }
}
