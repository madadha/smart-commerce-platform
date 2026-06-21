<?php

namespace App\Observers;

use App\Enums\PaymentStatus;
use App\Enums\PaymentTransactionStatus;
use App\Models\Payment;
use App\Services\Checkout\CheckoutInventoryService;

class PaymentInventoryObserver
{
    public function __construct(
        private readonly CheckoutInventoryService $inventoryService
    ) {}

    public function saved(Payment $payment): void
    {
        $order = $payment->order()->first();

        if ($order?->payment_status === PaymentStatus::Paid) {
            $this->inventoryService->fulfillOrderInventory($order);

            return;
        }

        if (
            $order
            && in_array($payment->status, [
                PaymentTransactionStatus::Failed,
                PaymentTransactionStatus::Cancelled,
            ], true)
            && ! $order->payments()
                ->whereIn('status', [
                    PaymentTransactionStatus::Pending->value,
                    PaymentTransactionStatus::Paid->value,
                ])
                ->exists()
        ) {
            $this->inventoryService->releaseOrderInventory($order);
        }
    }
}
