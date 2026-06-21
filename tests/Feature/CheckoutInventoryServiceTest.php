<?php

namespace Tests\Feature;

use App\Enums\DigitalCodeStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductDigitalCode;
use App\Models\ProductVariant;
use App\Services\Checkout\CheckoutInventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use RuntimeException;
use Tests\TestCase;

class CheckoutInventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reserves_product_stock_once_and_releases_it_idempotently(): void
    {
        $product = $this->createProduct(stock: 10);
        [$order, $item] = $this->createOrderItem($product, quantity: 3);
        $service = app(CheckoutInventoryService::class);

        $service->reserveOrderInventory($order);
        $service->reserveOrderInventory($order->fresh());

        $this->assertSame(7, $product->fresh()->stock_quantity);
        $this->assertSame('reserved', $item->fresh()->inventory_status);

        $service->releaseOrderInventory($order->fresh());
        $service->releaseOrderInventory($order->fresh());

        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertSame('released', $item->fresh()->inventory_status);
    }

    public function test_it_reserves_selected_variant_stock_without_touching_product_stock(): void
    {
        $product = $this->createProduct(stock: 20);
        $variant = ProductVariant::query()->forceCreate([
            'product_id' => $product->id,
            'name' => ['en' => '256 GB'],
            'sku' => 'VAR-'.uniqid(),
            'track_stock' => true,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);
        [$order] = $this->createOrderItem($product, quantity: 2, variant: $variant);

        app(CheckoutInventoryService::class)->reserveOrderInventory($order);

        $this->assertSame(3, $variant->fresh()->stock_quantity);
        $this->assertSame(20, $product->fresh()->stock_quantity);
    }

    public function test_digital_codes_are_reserved_before_payment_and_sold_only_on_fulfillment(): void
    {
        $product = $this->createProduct(stock: 0, type: 'digital_card');
        [$order, $item] = $this->createOrderItem($product, quantity: 2, itemType: 'digital_code');

        foreach (['CODE-ONE', 'CODE-TWO'] as $code) {
            ProductDigitalCode::query()->forceCreate([
                'product_id' => $product->id,
                'code' => $code.'-'.uniqid(),
                'status' => DigitalCodeStatus::Available,
                'is_active' => true,
            ]);
        }

        $service = app(CheckoutInventoryService::class);
        $service->reserveOrderInventory($order);

        $this->assertSame(2, ProductDigitalCode::query()
            ->where('order_item_id', $item->id)
            ->where('status', DigitalCodeStatus::Reserved->value)
            ->count());
        $this->assertSame('reserved', $item->fresh()->inventory_status);

        $service->fulfillOrderInventory($order->fresh());

        $this->assertSame(2, ProductDigitalCode::query()
            ->where('order_item_id', $item->id)
            ->where('status', DigitalCodeStatus::Sold->value)
            ->count());
        $this->assertSame('fulfilled', $item->fresh()->inventory_status);
    }

    public function test_releasing_a_digital_reservation_returns_codes_to_available_stock(): void
    {
        $product = $this->createProduct(stock: 0, type: 'digital_card');
        [$order, $item] = $this->createOrderItem($product, itemType: 'digital_code');
        $code = ProductDigitalCode::query()->forceCreate([
            'product_id' => $product->id,
            'code' => 'RELEASE-'.uniqid(),
            'status' => DigitalCodeStatus::Available,
            'is_active' => true,
        ]);
        $service = app(CheckoutInventoryService::class);

        $service->reserveOrderInventory($order);
        $service->releaseOrderInventory($order->fresh());

        $code->refresh();
        $this->assertSame(DigitalCodeStatus::Available, $code->status);
        $this->assertNull($code->order_id);
        $this->assertNull($code->order_item_id);
        $this->assertSame('released', $item->fresh()->inventory_status);
    }

    public function test_cancelling_an_order_releases_reserved_physical_stock(): void
    {
        $product = $this->createProduct(stock: 4);
        [$order] = $this->createOrderItem($product, quantity: 2);
        app(CheckoutInventoryService::class)->reserveOrderInventory($order);

        $order->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        $this->assertSame(4, $product->fresh()->stock_quantity);
        $this->assertSame('released', $order->items()->first()->inventory_status);
    }

    public function test_confirmed_payment_fulfills_reserved_digital_codes(): void
    {
        $product = $this->createProduct(stock: 0, type: 'digital_card');
        [$order, $item] = $this->createOrderItem($product, itemType: 'digital_code');
        ProductDigitalCode::query()->forceCreate([
            'product_id' => $product->id,
            'code' => 'PAID-'.uniqid(),
            'status' => DigitalCodeStatus::Available,
            'is_active' => true,
        ]);
        app(CheckoutInventoryService::class)->reserveOrderInventory($order);

        $payment = Payment::query()->forceCreate([
            'payment_number' => 'PAY-TEST-'.uniqid(),
            'order_id' => $order->id,
            'payment_method' => 'test',
            'status' => 'pending',
            'amount' => $order->grand_total,
            'is_active' => true,
        ]);
        $payment->update(['status' => 'paid', 'paid_at' => now()]);

        $this->assertSame('fulfilled', $item->fresh()->inventory_status);
        $this->assertDatabaseHas('product_digital_codes', [
            'order_item_id' => $item->id,
            'status' => DigitalCodeStatus::Sold->value,
        ]);
    }

    public function test_insufficient_stock_rolls_back_without_changing_inventory(): void
    {
        $product = $this->createProduct(stock: 1);
        [$order] = $this->createOrderItem($product, quantity: 2);

        try {
            app(CheckoutInventoryService::class)->reserveOrderInventory($order);
            $this->fail('Expected insufficient stock exception was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Insufficient stock', $exception->getMessage());
        }

        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertNull($order->items()->first()->inventory_status);
    }

    public function test_the_last_stock_unit_cannot_be_reserved_by_a_second_order(): void
    {
        $product = $this->createProduct(stock: 1);
        [$firstOrder] = $this->createOrderItem($product);
        [$secondOrder] = $this->createOrderItem($product);
        $service = app(CheckoutInventoryService::class);

        $service->reserveOrderInventory($firstOrder);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insufficient stock');
        $service->reserveOrderInventory($secondOrder);
    }

    public function test_terminal_failed_payment_releases_inventory_when_no_attempt_is_pending(): void
    {
        $product = $this->createProduct(stock: 3);
        [$order] = $this->createOrderItem($product, quantity: 2);
        app(CheckoutInventoryService::class)->reserveOrderInventory($order);
        $payment = Payment::query()->forceCreate([
            'payment_number' => 'PAY-FAILED-'.uniqid(),
            'order_id' => $order->id,
            'payment_method' => 'test',
            'status' => 'pending',
            'amount' => $order->grand_total,
            'is_active' => true,
        ]);

        $payment->update(['status' => 'failed', 'failed_at' => now()]);

        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertSame('released', $order->items()->first()->inventory_status);
    }

    public function test_expired_reservation_command_releases_only_old_unpaid_orders(): void
    {
        Carbon::setTestNow('2026-06-22 12:00:00');
        config(['commerce.inventory_reservation_minutes' => 30]);
        $expiredProduct = $this->createProduct(stock: 2);
        [$expiredOrder, $expiredItem] = $this->createOrderItem($expiredProduct);
        $freshProduct = $this->createProduct(stock: 2);
        [$freshOrder, $freshItem] = $this->createOrderItem($freshProduct);
        $service = app(CheckoutInventoryService::class);
        $service->reserveOrderInventory($expiredOrder);
        $service->reserveOrderInventory($freshOrder);
        $expiredItem->updateQuietly(['inventory_reserved_at' => now()->subMinutes(31)]);

        $this->artisan('commerce:release-expired-reservations')
            ->expectsOutput('Released inventory reservations for 1 order(s).')
            ->assertSuccessful();

        $this->assertSame('released', $expiredItem->fresh()->inventory_status);
        $this->assertSame(2, $expiredProduct->fresh()->stock_quantity);
        $this->assertSame('reserved', $freshItem->fresh()->inventory_status);
        $this->assertSame(1, $freshProduct->fresh()->stock_quantity);
        Carbon::setTestNow();
    }

    public function test_a_fully_refunded_payment_marks_the_order_as_refunded_without_restocking(): void
    {
        $product = $this->createProduct(stock: 2);
        [$order] = $this->createOrderItem($product);
        app(CheckoutInventoryService::class)->reserveOrderInventory($order);
        $payment = Payment::query()->forceCreate([
            'payment_number' => 'PAY-REFUND-'.uniqid(),
            'order_id' => $order->id,
            'payment_method' => 'test',
            'status' => 'paid',
            'amount' => $order->grand_total,
            'paid_at' => now(),
            'is_active' => true,
        ]);

        $payment->update([
            'status' => 'refunded',
            'refunded_amount' => $order->grand_total,
            'refunded_at' => now(),
        ]);

        $this->assertSame('refunded', $order->fresh()->payment_status->value);
        $this->assertSame(0.0, (float) $order->fresh()->paid_total);
        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertSame('fulfilled', $order->items()->first()->inventory_status);
    }

    private function createProduct(int $stock, string $type = 'physical'): Product
    {
        return Product::query()->forceCreate([
            'name' => ['en' => 'Test Product'],
            'slug' => 'test-product-'.uniqid(),
            'sku' => 'SKU-'.uniqid(),
            'product_type' => $type,
            'status' => 'active',
            'price' => 100,
            'track_stock' => true,
            'stock_quantity' => $stock,
            'requires_shipping' => $type === 'physical',
            'is_active' => true,
        ]);
    }

    private function createOrderItem(
        Product $product,
        int $quantity = 1,
        ?ProductVariant $variant = null,
        string $itemType = 'product'
    ): array {
        $order = Order::query()->forceCreate([
            'order_number' => 'ORD-TEST-'.uniqid(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100 * $quantity,
            'grand_total' => 100 * $quantity,
            'is_active' => true,
        ]);

        $item = OrderItem::query()->forceCreate([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'product_name' => ['en' => 'Test Product'],
            'sku' => $variant?->sku ?? $product->sku,
            'item_type' => $itemType,
            'quantity' => $quantity,
            'unit_price' => 100,
            'line_total' => 100 * $quantity,
        ]);

        return [$order, $item];
    }
}
