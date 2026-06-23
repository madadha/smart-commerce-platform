<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentProviderSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class StorefrontCartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_variant_is_preserved_with_its_price_sku_and_options(): void
    {
        [$product, $variant] = $this->createProductWithVariant();

        $response = $this->from('/store/products/'.$product->slug.'?lang=ar')
            ->post(route('storefront.cart.add'), [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => 2,
                'lang' => 'ar',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $cart = Cart::query()->findOrFail(session('storefront_cart_id'));
        $item = $cart->items()->firstOrFail();

        $this->assertSame($variant->id, $item->product_variant_id);
        $this->assertSame($variant->sku, $item->sku);
        $this->assertSame(450.0, (float) $item->unit_price);
        $this->assertSame(900.0, (float) $item->line_total);
        $this->assertSame(['storage' => '256gb'], $item->options);
        $this->assertSame(900.0, (float) $cart->fresh()->grand_total);
    }

    public function test_product_with_active_variants_cannot_be_added_without_a_selection(): void
    {
        [$product] = $this->createProductWithVariant();

        $response = $this->from('/store/products/'.$product->slug.'?lang=en')
            ->post(route('storefront.cart.add'), [
                'product_id' => $product->id,
                'quantity' => 1,
                'lang' => 'en',
            ]);

        $response->assertRedirect('/store/products/'.$product->slug.'?lang=en');
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_variant_from_another_product_is_rejected(): void
    {
        [$product] = $this->createProductWithVariant();
        [, $otherVariant] = $this->createProductWithVariant('other');

        $this->post(route('storefront.cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $otherVariant->id,
            'quantity' => 1,
        ])->assertNotFound();

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_checkout_creates_order_with_variant_snapshot_and_single_stock_deduction(): void
    {
        Mail::fake();
        [$product, $variant] = $this->createProductWithVariant();
        $this->post(route('storefront.cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'lang' => 'ar',
        ])->assertRedirect();

        $response = $this->post(route('storefront.checkout.place'), [
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.test',
            'customer_phone' => '0500000000',
            'city' => 'Jerusalem',
            'address' => 'Test Street 1',
            'payment_method' => 'cash',
            'lang' => 'ar',
        ]);

        $order = Order::query()->with('items')->firstOrFail();
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertSame('unpaid', $order->payment_status->value);
        $this->assertSame($product->currency_id, $order->currency_id);
        $this->assertSame(900.0, (float) $order->grand_total);
        $this->assertSame($variant->id, $order->items->first()->product_variant_id);
        $this->assertSame(['storage' => '256gb'], $order->items->first()->options);
        $this->assertSame('reserved', $order->items->first()->inventory_status);
        $this->assertSame(3, $variant->fresh()->stock_quantity);
        $this->assertSame(20, $product->fresh()->stock_quantity);
        $this->assertNull(session('storefront_cart_id'));
        $this->assertSame('converted', Cart::query()->firstOrFail()->status->value);
    }

    public function test_checkout_validation_does_not_convert_the_cart(): void
    {
        [$product, $variant] = $this->createProductWithVariant();
        $this->post(route('storefront.cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->post(route('storefront.checkout.place'), [
            'customer_name' => '',
            'customer_phone' => '',
            'city' => '',
            'address' => '',
            'payment_method' => '',
        ])->assertSessionHasErrors([
            'customer_name',
            'customer_phone',
            'city',
            'address',
            'payment_method',
        ]);

        $this->assertSame('active', Cart::query()->firstOrFail()->status->value);
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5, $variant->fresh()->stock_quantity);
    }

    public function test_checkout_rejects_payment_methods_without_an_enabled_gateway(): void
    {
        [$product, $variant] = $this->createProductWithVariant();
        $this->post(route('storefront.cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->post(route('storefront.checkout.place'), [
            'customer_name' => 'Card Customer',
            'customer_phone' => '0503333333',
            'city' => 'Jerusalem',
            'address' => 'Card Street 4',
            'payment_method' => 'credit_card',
        ])->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_authenticated_checkout_links_the_order_and_customer_profile_to_the_user(): void
    {
        Mail::fake();
        $user = User::factory()->create(['name' => 'Account Customer']);
        [$product, $variant] = $this->createProductWithVariant();
        $this->actingAs($user)->post(route('storefront.cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->post(route('storefront.checkout.place'), [
            'customer_name' => 'Account Customer',
            'customer_email' => $user->email,
            'customer_phone' => '0501111111',
            'city' => 'Amman',
            'address' => 'Account Street 2',
            'payment_method' => 'cash',
            'lang' => 'en',
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $customer = Customer::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame('0501111111', $customer->phone);
        $this->assertSame('Amman', $customer->city);
    }

    public function test_checkout_recalculates_coupon_shipping_tax_and_total_on_the_server(): void
    {
        Mail::fake();
        [$product, $variant] = $this->createProductWithVariant();
        $this->post(route('storefront.cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $coupon = Coupon::query()->forceCreate([
            'code' => 'CHECKOUT10',
            'name' => ['en' => 'Checkout 10%'],
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
        ]);
        $shipping = ShippingMethod::query()->forceCreate([
            'name' => ['en' => 'Standard Delivery'],
            'slug' => 'standard-delivery-'.uniqid(),
            'type' => 'standard',
            'base_cost' => 20,
            'is_active' => true,
        ]);
        Cart::query()->firstOrFail()->forceFill([
            'coupon_id' => $coupon->id,
            'tax_total' => 45,
        ])->save();

        $this->post(route('storefront.checkout.place'), [
            'customer_name' => 'Totals Customer',
            'customer_phone' => '0502222222',
            'city' => 'Jerusalem',
            'address' => 'Totals Street 3',
            'shipping_method_id' => $shipping->id,
            'payment_method' => 'cash',
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame(900.0, (float) $order->subtotal);
        $this->assertSame(90.0, (float) $order->discount_total);
        $this->assertSame(45.0, (float) $order->tax_total);
        $this->assertSame(20.0, (float) $order->shipping_total);
        $this->assertSame(875.0, (float) $order->grand_total);
        $this->assertSame('CHECKOUT10', $order->coupon_code);
    }

    public function test_verified_payplus_checkout_redirects_to_the_hosted_payment_page(): void
    {
        Mail::fake();
        [$product, $variant] = $this->createProductWithVariant();
        PaymentProviderSetting::query()->where('provider', 'payplus')->firstOrFail()->update([
            'is_enabled' => true,
            'connection_status' => 'verified',
            'sandbox_credentials' => [
                'api_key' => 'sandbox-api-key',
                'secret_key' => 'sandbox-secret-key',
                'payment_page_uid' => 'sandbox-page-uid',
                'terminal_uid' => 'sandbox-terminal-uid',
            ],
        ]);
        Http::fake([
            'https://restapidev.payplus.co.il/api/v1.0/PaymentPages/generateLink' => Http::response([
                'results' => ['status' => 'success', 'code' => 0],
                'data' => [
                    'page_request_uid' => 'checkout-page-request',
                    'payment_page_link' => 'https://payments.example.test/checkout-page-request',
                ],
            ]),
        ]);
        $this->post(route('storefront.cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->post(route('storefront.checkout.place'), [
            'customer_name' => 'PayPlus Customer',
            'customer_phone' => '0504444444',
            'city' => 'Jerusalem',
            'address' => 'PayPlus Street 5',
            'payment_method' => 'payplus',
        ])->assertRedirect('https://payments.example.test/checkout-page-request');

        $this->assertDatabaseHas('payments', [
            'provider' => 'payplus',
            'provider_reference' => 'checkout-page-request',
            'status' => 'pending',
        ]);
    }

    private function createProductWithVariant(string $suffix = 'primary'): array
    {
        $currency = Currency::query()->firstOrCreate(
            ['code' => 'ILS'],
            [
                'name' => ['en' => 'Israeli Shekel'],
                'symbol' => 'ILS',
                'exchange_rate' => 1,
                'is_default' => true,
                'is_active' => true,
            ]
        );
        $product = Product::query()->forceCreate([
            'name' => ['ar' => 'هاتف تجريبي', 'en' => 'Test Phone'],
            'slug' => 'test-phone-'.$suffix.'-'.uniqid(),
            'sku' => 'PHONE-'.$suffix.'-'.uniqid(),
            'product_type' => 'physical',
            'status' => 'active',
            'currency_id' => $currency->id,
            'price' => 500,
            'track_stock' => true,
            'stock_quantity' => 20,
            'requires_shipping' => true,
            'is_active' => true,
        ]);
        $variant = ProductVariant::query()->forceCreate([
            'product_id' => $product->id,
            'name' => ['ar' => '256 جيجابايت', 'en' => '256 GB'],
            'sku' => 'PHONE-256-'.$suffix.'-'.uniqid(),
            'option_values' => ['storage' => '256gb'],
            'price' => 450,
            'track_stock' => true,
            'stock_quantity' => 5,
            'is_active' => true,
            'is_default' => true,
        ]);

        return [$product, $variant];
    }
}
