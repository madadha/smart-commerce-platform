<?php

namespace Tests\Feature;

use App\Models\PaymentWebhookEvent;
use App\Services\Payments\WebhookEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class WebhookEventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_webhook_event_is_processed_only_once(): void
    {
        $calls = 0;
        $service = app(WebhookEventService::class);
        $handler = function () use (&$calls): void {
            $calls++;
        };

        $first = $service->process('test', 'event-1', 'payment.paid', ['id' => 1], $handler);
        $duplicate = $service->process('test', 'event-1', 'payment.paid', ['id' => 1], $handler);

        $this->assertTrue($first);
        $this->assertFalse($duplicate);
        $this->assertSame(1, $calls);
        $this->assertDatabaseHas('payment_webhook_events', [
            'provider' => 'test',
            'event_id' => 'event-1',
            'status' => 'processed',
        ]);
    }

    public function test_failed_webhook_is_recorded_and_can_be_retried(): void
    {
        $service = app(WebhookEventService::class);

        try {
            $service->process('test', 'event-2', 'payment.failed', [], function (): void {
                throw new RuntimeException('Temporary processing failure');
            });
            $this->fail('Expected webhook handler exception was not thrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Temporary processing failure', $exception->getMessage());
        }

        $event = PaymentWebhookEvent::query()->firstOrFail();
        $this->assertSame('failed', $event->status);
        $this->assertNotNull($event->failed_at);

        $processed = $service->process('test', 'event-2', 'payment.failed', [], fn () => true);

        $this->assertTrue($processed);
        $this->assertSame('processed', $event->fresh()->status);
    }
}
