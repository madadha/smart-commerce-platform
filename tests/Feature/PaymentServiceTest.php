<?php

namespace Tests\Feature;

use App\Enums\PaymentTransactionStatus;
use App\Models\Order;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_attempt_creation_is_idempotent(): void
    {
        $order = $this->createOrder();
        $service = app(PaymentService::class);

        $first = $service->createAttempt($order, 'cash', 'order-1-cash');
        $second = $service->createAttempt($order, 'cash', 'order-1-cash');

        $this->assertSame($first->id, $second->id);
        $this->assertSame('manual', $first->provider);
        $this->assertSame($first->payment_number, $first->provider_reference);
        $this->assertSame(PaymentTransactionStatus::Pending, $first->status);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_an_idempotency_key_cannot_be_reused_for_another_order(): void
    {
        $service = app(PaymentService::class);
        $service->createAttempt($this->createOrder(), 'cash', 'shared-key');

        $this->expectException(InvalidArgumentException::class);
        $service->createAttempt($this->createOrder(), 'cash', 'shared-key');
    }

    public function test_paid_callback_is_idempotent_and_updates_order_totals(): void
    {
        $order = $this->createOrder();
        $service = app(PaymentService::class);
        $payment = $service->createAttempt($order, 'cash', 'paid-key');

        $first = $service->markPaid($payment, 'provider-reference-1', ['event' => 'paid']);
        $second = $service->markPaid($first, 'provider-reference-1', ['event' => 'paid']);

        $this->assertSame(PaymentTransactionStatus::Paid, $second->status);
        $this->assertSame('provider-reference-1', $second->provider_reference);
        $this->assertSame(100.0, (float) $order->fresh()->paid_total);
        $this->assertSame('paid', $order->fresh()->payment_status->value);
    }

    public function test_partial_and_full_refunds_are_recorded_safely(): void
    {
        $order = $this->createOrder();
        $service = app(PaymentService::class);
        $payment = $service->markPaid(
            $service->createAttempt($order, 'cash', 'refund-key'),
            'provider-reference-2',
        );

        $payment = $service->refund($payment, 40);
        $this->assertSame(PaymentTransactionStatus::PartiallyRefunded, $payment->status);
        $this->assertSame(40.0, (float) $payment->refunded_amount);
        $this->assertSame(60.0, (float) $order->fresh()->paid_total);
        $this->assertSame('partially_paid', $order->fresh()->payment_status->value);

        $payment = $service->refund($payment, 60);
        $this->assertSame(PaymentTransactionStatus::Refunded, $payment->status);
        $this->assertSame(100.0, (float) $payment->refunded_amount);
        $this->assertSame(0.0, (float) $order->fresh()->paid_total);
        $this->assertSame('refunded', $order->fresh()->payment_status->value);
    }

    public function test_disabled_payment_method_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(PaymentService::class)->createAttempt(
            $this->createOrder(),
            'credit_card',
            'disabled-card',
        );
    }

    private function createOrder(): Order
    {
        return Order::query()->forceCreate([
            'order_number' => 'ORD-PAYMENT-'.uniqid(),
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100,
            'grand_total' => 100,
            'is_active' => true,
        ]);
    }
}
