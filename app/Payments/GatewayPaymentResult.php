<?php

namespace App\Payments;

use App\Enums\PaymentTransactionStatus;

readonly class GatewayPaymentResult
{
    public function __construct(
        public PaymentTransactionStatus $status,
        public ?string $providerReference = null,
        public ?string $transactionId = null,
        public ?string $redirectUrl = null,
        public array $payload = [],
        public ?string $failureCode = null,
        public ?string $failureMessage = null,
    ) {}
}
