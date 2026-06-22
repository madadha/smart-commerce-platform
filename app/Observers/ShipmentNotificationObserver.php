<?php

namespace App\Observers;

use App\Enums\ShipmentStatus;
use App\Mail\ShipmentStatusUpdatedMail;
use App\Models\Shipment;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ShipmentNotificationObserver
{
    public function updated(Shipment $shipment): void
    {
        if (! $shipment->wasChanged('status') || ! in_array($shipment->status, [
            ShipmentStatus::Shipped,
            ShipmentStatus::InTransit,
            ShipmentStatus::OutForDelivery,
            ShipmentStatus::Delivered,
            ShipmentStatus::Failed,
            ShipmentStatus::Returned,
            ShipmentStatus::Cancelled,
        ], true)) {
            return;
        }

        $shipment->loadMissing('order.customer');
        $email = $shipment->order->customer_email ?? $shipment->order->customer?->email;

        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send(new ShipmentStatusUpdatedMail($shipment->fresh()));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
