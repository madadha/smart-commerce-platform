<?php

namespace App\Services\Payments;

use App\Enums\PaymentTransactionStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Payments\GatewayPaymentResult;
use App\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class PaymentService
{
    public function __construct(
        private readonly PaymentGatewayManager $gatewayManager,
    ) {}

    public function createAttempt(
        Order $order,
        string $method,
        string $idempotencyKey,
        array $context = []
    ): Payment {
        $gateway = $this->gatewayManager->gatewayForMethod($method);

        $payment = DB::transaction(function () use ($order, $method, $idempotencyKey, $gateway): Payment {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $existing = Payment::query()->where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                if ($existing->order_id !== $lockedOrder->id || $existing->payment_method !== $method) {
                    throw new InvalidArgumentException('Idempotency key is already used for another payment.');
                }

                return $existing;
            }

            return Payment::query()->create([
                'order_id' => $lockedOrder->id,
                'customer_id' => $lockedOrder->customer_id,
                'currency_id' => $lockedOrder->currency_id,
                'payment_method' => $method,
                'provider' => $gateway->name(),
                'status' => PaymentTransactionStatus::Pending,
                'amount' => $lockedOrder->grand_total,
                'idempotency_key' => $idempotencyKey,
                'is_active' => true,
            ]);
        });

        if ($payment->provider_reference || $payment->status !== PaymentTransactionStatus::Pending) {
            return $payment;
        }

        try {
            return $this->applyGatewayResult(
                $payment,
                $gateway->createPayment($payment, $context),
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->markFailed(
                $payment,
                failureCode: 'gateway_error',
                failureMessage: $exception->getMessage(),
            );
        }
    }

    public function markPaid(
        Payment $payment,
        string $transactionId,
        array $payload = []
    ): Payment {
        if ($payment->status === PaymentTransactionStatus::Paid) {
            return $payment;
        }

        return $this->applyGatewayResult($payment, new GatewayPaymentResult(
            status: PaymentTransactionStatus::Paid,
            providerReference: $payment->provider_reference ?? $transactionId,
            transactionId: $transactionId,
            payload: $payload,
        ));
    }

    public function recordRefund(
        Payment $payment,
        float $amount,
        string $transactionId,
        array $payload = [],
    ): Payment {
        $newRefundedAmount = min((float) $payment->refunded_amount + $amount, (float) $payment->amount);
        $payment->refunded_amount = $newRefundedAmount;
        $payment->refunded_at = now();

        return $this->applyGatewayResult($payment, new GatewayPaymentResult(
            status: $newRefundedAmount >= (float) $payment->amount
                ? PaymentTransactionStatus::Refunded
                : PaymentTransactionStatus::PartiallyRefunded,
            providerReference: $payment->provider_reference,
            transactionId: $transactionId,
            payload: $payload,
        ));
    }

    public function markFailed(
        Payment $payment,
        ?string $failureCode = null,
        ?string $failureMessage = null,
        array $payload = []
    ): Payment {
        if (in_array($payment->status, [
            PaymentTransactionStatus::Paid,
            PaymentTransactionStatus::PartiallyRefunded,
            PaymentTransactionStatus::Refunded,
        ], true)) {
            throw new InvalidArgumentException('A paid or refunded payment cannot be marked as failed.');
        }

        return $this->applyGatewayResult($payment, new GatewayPaymentResult(
            status: PaymentTransactionStatus::Failed,
            payload: $payload,
            failureCode: $failureCode,
            failureMessage: $failureMessage,
        ));
    }

    public function refund(Payment $payment, float $amount, array $context = []): Payment
    {
        if (! in_array($payment->status, [
            PaymentTransactionStatus::Paid,
            PaymentTransactionStatus::PartiallyRefunded,
        ], true)) {
            throw new InvalidArgumentException('Only a paid payment can be refunded.');
        }

        $newRefundedAmount = (float) $payment->refunded_amount + $amount;

        if ($amount <= 0 || $newRefundedAmount > (float) $payment->amount) {
            throw new InvalidArgumentException('Refund amount is invalid.');
        }

        $gateway = $this->gatewayManager->gatewayForMethod($payment->payment_method);
        $result = $gateway->refund($payment, $amount, $context);

        $payment->refunded_amount = $newRefundedAmount;
        $payment->refunded_at = now();

        return $this->applyGatewayResult($payment, $result);
    }

    private function applyGatewayResult(Payment $payment, GatewayPaymentResult $result): Payment
    {
        $payload = array_replace_recursive(
            $payment->provider_payload ?? [],
            $result->payload,
        );

        $payment->forceFill([
            'status' => $result->status,
            'provider_reference' => $result->providerReference ?? $payment->provider_reference,
            'transaction_id' => $result->transactionId ?? $payment->transaction_id,
            'checkout_url' => $result->redirectUrl ?? $payment->checkout_url,
            'checkout_expires_at' => $result->redirectUrl ? now()->addMinutes(30) : $payment->checkout_expires_at,
            'provider_payload' => $payload,
            'failure_code' => $result->failureCode,
            'failure_message' => $result->failureMessage,
            'paid_at' => $result->status === PaymentTransactionStatus::Paid
                ? ($payment->paid_at ?? now())
                : $payment->paid_at,
            'failed_at' => $result->status === PaymentTransactionStatus::Failed
                ? now()
                : $payment->failed_at,
            'refunded_at' => $result->status === PaymentTransactionStatus::Refunded
                ? ($payment->refunded_at ?? now())
                : $payment->refunded_at,
        ])->save();

        return $payment->fresh();
    }
}
