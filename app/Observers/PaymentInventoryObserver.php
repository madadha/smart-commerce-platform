<?php

namespace App\Observers;

use App\Enums\PaymentStatus;
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
        }
    }
}
