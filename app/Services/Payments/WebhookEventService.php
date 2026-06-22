<?php

namespace App\Services\Payments;

use App\Models\PaymentWebhookEvent;
use Illuminate\Support\Facades\DB;
use Throwable;

class WebhookEventService
{
    public function process(
        string $provider,
        string $eventId,
        ?string $eventType,
        array $payload,
        callable $handler,
    ): bool {
        $event = DB::transaction(function () use ($provider, $eventId, $eventType, $payload) {
            PaymentWebhookEvent::query()->firstOrCreate(
                ['provider' => $provider, 'event_id' => $eventId],
                [
                    'event_type' => $eventType,
                    'status' => 'pending',
                    'payload' => $payload,
                    'received_at' => now(),
                ],
            );

            $event = PaymentWebhookEvent::query()
                ->where('provider', $provider)
                ->where('event_id', $eventId)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($event->status, ['processing', 'processed'], true)) {
                return null;
            }

            $event->forceFill([
                'status' => 'processing',
                'event_type' => $eventType ?? $event->event_type,
                'payload' => $payload,
                'error_message' => null,
                'failed_at' => null,
            ])->save();

            return $event;
        });

        if (! $event) {
            return false;
        }

        try {
            $handler($event);

            $event->forceFill([
                'status' => 'processed',
                'processed_at' => now(),
            ])->save();

            return true;
        } catch (Throwable $exception) {
            $event->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }
    }
}
