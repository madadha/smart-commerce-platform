<?php

namespace App\Contracts\Payments;

use App\Models\Payment;
use App\Payments\GatewayPaymentResult;

interface PaymentGateway
{
    public function name(): string;

    public function createPayment(Payment $payment, array $context = []): GatewayPaymentResult;

    public function refund(Payment $payment, float $amount, array $context = []): GatewayPaymentResult;
}
