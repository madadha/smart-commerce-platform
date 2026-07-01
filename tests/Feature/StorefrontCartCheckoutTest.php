<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Game;
use App\Models\GameRegion;
use App\Models\Order;
use App\Models\PaymentProviderSetting;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\StorefrontSetting;
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

    public function test_game_topup_add_to_cart_captures_player_details_and_provider_sku(): void
    {
        [$product, $variant] = $this->createGameTopUpProduct();

        $this->from('/store/products/'.$product->slug.'?lang=en')
            ->post(route('storefront.cart.add'), [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => 1,
                'game_player_id' => '5123456789',
                'game_region' => 'Middle East',
                'lang' => 'en',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $cart = Cart::query()->findOrFail(session('storefront_cart_id'));
        $item = $cart->items()->firstOrFail();

        $this->assertSame('game_topup', $item->item_type);
        $this->assertSame(['package' => '60uc'], collect($item->options)->except('game_topup')->all());
        $this->assertSame('5123456789', $item->options['game_topup']['player_id']);
        $this->assertSame('Middle East', $item->options['game_topup']['region']);
        $this->assertSame('PUBG-60-UC', $item->options['game_topup']['provider_sku']);
        $this->assertSame('manual', $item->options['game_topup']['delivery_mode']);
    }

    public function test_game_topup_product_page_uses_managed_game_regions_and_server_options(): void
    {
        [$product] = $this->createLinkedGameTopUpProduct();

        $response = $this->get('/store/products/'.$product->slug.'?lang=en');

        $response->assertOk();
        $response->assertSee('name="game_region_id"', false);
        $response->assertSee('Middle East - MIDDLE_EAST');
        $response->assertSee('name="game_server_key"', false);
        $response->assertSee('Asia Server');
    }

    public function test_game_topup_add_to_cart_stores_linked_game_region_and_server_snapshot(): void
    {
        [$product, $variant, $region] = $this->createLinkedGameTopUpProduct();

        $this->from('/store/products/'.$product->slug.'?lang=en')
            ->post(route('storefront.cart.add'), [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => 1,
                'game_player_id' => '99887766',
                'game_region_id' => $region->id,
                'game_server_key' => 'asia',
                'lang' => 'en',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $cart = Cart::query()->findOrFail(session('storefront_cart_id'));
        $gameTopUp = $cart->items()->firstOrFail()->options['game_topup'];

        $this->assertSame($product->game_id, $gameTopUp['game_id']);
        $this->assertSame('PUBG MOBILE', $gameTopUp['game_title']);
        $this->assertSame($region->id, $gameTopUp['region_id']);
        $this->assertSame('MIDDLE_EAST', $gameTopUp['region_code']);
        $this->assertSame('Middle East', $gameTopUp['region']);
        $this->assertSame('asia', $gameTopUp['server_key']);
        $this->assertSame('Asia Server', $gameTopUp['server']);
    }

    public function test_game_topup_is_blocked_when_disabled_from_storefront_settings(): void
    {
        StorefrontSetting::query()->forceCreate([
            'store_name' => ['en' => 'Smart Commerce'],
            'store_tagline' => ['en' => 'Marketplace Platform'],
            'enable_game_topups' => false,
            'is_active' => true,
        ]);

        [$product, $variant] = $this->createGameTopUpProduct('disabled');

        $this->from('/store/products/'.$product->slug.'?lang=en')
            ->post(route('storefront.cart.add'), [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => 1,
                'game_player_id' => '5123456789',
                'game_region' => 'Middle East',
                'lang' => 'en',
            ])
            ->assertRedirect('/store/products/'.$product->slug.'?lang=en')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cart_items', 0);
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

    public function test_header_cart_badge_shows_current_cart_quantity(): void
    {
        [$product, $variant] = $this->createProductWithVariant();

        $this->post(route('storefront.cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'lang' => 'en',
        ])->assertRedirect();

        $response = $this->get('/?lang=en');

        $response->assertOk();
        $response->assertSee('scp-cart-count-badge', false);
        $response->assertSee('>2<', false);
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

    public function test_customer_can_apply_and_remove_coupon_from_checkout(): void
    {
        [$product, $variant] = $this->createProductWithVariant();
        $this->post(route('storefront.cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        $coupon = Coupon::query()->forceCreate([
            'code' => 'save10',
            'name' => ['en' => 'Save 10%'],
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'is_active' => true,
        ]);

        $this->from('/store/checkout?lang=en')
            ->post(route('storefront.checkout.coupon.apply'), [
                'code' => ' save10 ',
                'lang' => 'en',
            ])
            ->assertRedirect('/store/checkout?lang=en')
            ->assertSessionHas('success');

        $cart = Cart::query()->firstOrFail()->fresh();
        $this->assertSame($coupon->id, $cart->coupon_id);
        $this->assertSame('SAVE10', $cart->coupon_code);
        $this->assertSame(45.0, (float) $cart->discount_total);
        $this->assertSame(405.0, (float) $cart->grand_total);

        $this->delete(route('storefront.checkout.coupon.remove'), ['lang' => 'en'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $cart = $cart->fresh();
        $this->assertNull($cart->coupon_id);
        $this->assertNull($cart->coupon_code);
        $this->assertSame(0.0, (float) $cart->discount_total);
        $this->assertSame(450.0, (float) $cart->grand_total);
    }

    public function test_checkout_rejects_invalid_coupon_for_current_cart(): void
    {
        [$product, $variant] = $this->createProductWithVariant();
        $this->post(route('storefront.cart.add'), [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        Coupon::query()->forceCreate([
            'code' => 'MIN1000',
            'name' => ['en' => 'Minimum 1000'],
            'discount_type' => 'fixed_amount',
            'discount_value' => 50,
            'minimum_order_total' => 1000,
            'is_active' => true,
        ]);

        $this->post(route('storefront.checkout.coupon.apply'), [
            'code' => 'MIN1000',
        ])->assertSessionHas('error');

        $cart = Cart::query()->firstOrFail()->fresh();
        $this->assertNull($cart->coupon_id);
        $this->assertSame(0.0, (float) $cart->discount_total);
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
        $currency = Currency::query()->where('code', 'ILS')->firstOrFail();
        $country = Country::query()->forceCreate([
            'name' => ['en' => 'Israel'],
            'code' => 'IL',
            'currency_id' => $currency->id,
            'tax_rate' => 18,
            'is_active' => true,
        ]);
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
            'country_id' => $country->id,
            'is_active' => true,
        ]);
        Cart::query()->firstOrFail()->forceFill([
            'coupon_id' => $coupon->id,
        ])->save();

        $this->post(route('storefront.checkout.place'), [
            'customer_name' => 'Totals Customer',
            'customer_phone' => '0502222222',
            'city' => 'Jerusalem',
            'address' => 'Totals Street 3',
            'country_id' => $country->id,
            'shipping_method_id' => $shipping->id,
            'payment_method' => 'cash',
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame(900.0, (float) $order->subtotal);
        $this->assertSame(90.0, (float) $order->discount_total);
        $this->assertSame(162.0, (float) $order->tax_total);
        $this->assertSame(20.0, (float) $order->shipping_total);
        $this->assertSame(992.0, (float) $order->grand_total);
        $this->assertSame('CHECKOUT10', $order->coupon_code);
        $this->assertSame(1, $coupon->fresh()->used_count);
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

    private function createGameTopUpProduct(string $suffix = 'primary'): array
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
            'name' => ['ar' => 'شدات ببجي', 'en' => 'PUBG UC Top-Up'],
            'slug' => 'pubg-uc-topup-'.$suffix.'-'.uniqid(),
            'sku' => 'PUBG-TOPUP-'.$suffix.'-'.uniqid(),
            'product_type' => 'game_topup',
            'status' => 'active',
            'currency_id' => $currency->id,
            'price' => 10,
            'track_stock' => false,
            'stock_quantity' => 0,
            'requires_shipping' => false,
            'game_title' => ['en' => 'PUBG MOBILE', 'ar' => 'PUBG MOBILE'],
            'game_currency_name' => ['en' => 'UC', 'ar' => 'UC'],
            'game_delivery_mode' => 'manual',
            'game_provider' => 'Manual Team',
            'game_provider_sku' => 'PUBG-DEFAULT',
            'game_requires_player_id' => true,
            'game_requires_region' => true,
            'game_requires_server' => false,
            'game_can_validate_player' => false,
            'is_active' => true,
        ]);

        $variant = ProductVariant::query()->forceCreate([
            'product_id' => $product->id,
            'name' => ['ar' => '60 UC', 'en' => '60 UC'],
            'sku' => 'PUBG-60-'.$suffix.'-'.uniqid(),
            'provider_sku' => 'PUBG-60-UC',
            'option_values' => ['package' => '60uc'],
            'price' => 10,
            'track_stock' => false,
            'stock_quantity' => 0,
            'is_active' => true,
            'is_default' => true,
        ]);

        return [$product, $variant];
    }

    private function createLinkedGameTopUpProduct(string $suffix = 'linked'): array
    {
        [$product, $variant] = $this->createGameTopUpProduct($suffix);

        $game = Game::query()->forceCreate([
            'name' => ['en' => 'PUBG MOBILE', 'ar' => 'PUBG MOBILE', 'he' => 'PUBG MOBILE'],
            'slug' => 'pubg-mobile-'.$suffix.'-'.uniqid(),
            'description' => ['en' => 'Recharge PUBG UC'],
            'default_provider' => 'Manual Team',
            'supports_player_validation' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $region = GameRegion::query()->forceCreate([
            'name' => ['en' => 'Middle East', 'ar' => 'الشرق الأوسط', 'he' => 'Middle East'],
            'code' => 'middle-east',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $game->regions()->attach($region->id, [
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product->forceFill([
            'game_id' => $game->id,
            'game_requires_region' => true,
            'game_requires_server' => true,
            'game_server_options' => [
                'asia' => 'Asia Server',
                'europe' => 'Europe Server',
            ],
        ])->save();

        $product->gameRegions()->attach($region->id, [
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return [$product->fresh(['game.activeRegions', 'gameRegions']), $variant, $region];
    }
}
