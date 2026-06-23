<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Checkout\CheckoutInventoryService;
use Illuminate\Console\Command;

class ReleaseExpiredInventoryReservations extends Command
{
    protected $signature = 'commerce:release-expired-reservations';

    protected $description = 'Release unpaid inventory reservations that exceeded their configured lifetime';

    public function handle(CheckoutInventoryService $inventoryService): int
    {
        $minutes = max((int) config('commerce.inventory_reservation_minutes', 30), 1);
        $cutoff = now()->subMinutes($minutes);
        $releasedOrders = 0;

        OrderItem::query()
            ->where('inventory_status', 'reserved')
            ->where('inventory_reserved_at', '<=', $cutoff)
            ->whereHas('order', function ($query) {
                $query->where('status', 'pending')
                    ->whereIn('payment_status', ['unpaid', 'failed']);
            })
            ->select('order_id')
            ->distinct()
            ->orderBy('order_id')
            ->chunkById(100, function ($items) use ($inventoryService, &$releasedOrders): void {
                $orders = Order::query()->whereIn('id', $items->pluck('order_id'))->get();

                foreach ($orders as $order) {
                    $inventoryService->releaseOrderInventory($order);
                    $releasedOrders++;
                }
            }, 'order_id');

        $this->info("Released inventory reservations for {$releasedOrders} order(s).");

        return self::SUCCESS;
    }
}
