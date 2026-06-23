<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Checkout\CheckoutInventoryService;

class OrderInventoryObserver
{
    public function __construct(
        private readonly CheckoutInventoryService $inventoryService
    ) {}

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') && $order->status === OrderStatus::Cancelled) {
            $this->inventoryService->releaseOrderInventory($order);
        }
    }
}
