<?php

namespace App\Payments\Gateways;

use App\Contracts\Payments\PaymentGateway;
use App\Enums\PaymentTransactionStatus;
use App\Models\Payment;
use App\Payments\GatewayPaymentResult;

class ManualPaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'manual';
    }

    public function createPayment(Payment $payment, array $context = []): GatewayPaymentResult
    {
        return new GatewayPaymentResult(
            status: PaymentTransactionStatus::Pending,
            providerReference: $payment->payment_number,
            payload: ['requires_manual_confirmation' => true],
        );
    }

    public function refund(Payment $payment, float $amount, array $context = []): GatewayPaymentResult
    {
        return new GatewayPaymentResult(
            status: ((float) $payment->refunded_amount + $amount) >= (float) $payment->amount
                ? PaymentTransactionStatus::Refunded
                : PaymentTransactionStatus::PartiallyRefunded,
            providerReference: $payment->provider_reference ?? $payment->payment_number,
            payload: ['manual_refund' => true, 'amount' => $amount],
        );
    }
}
