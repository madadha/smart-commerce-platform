<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
    case Shipped = 'shipped';
    case InTransit = 'in_transit';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Returned = 'returned';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending', self::Ready => 'Ready for pickup', self::Shipped => 'Shipped',
            self::InTransit => 'In transit', self::OutForDelivery => 'Out for delivery',
            self::Delivered => 'Delivered', self::Failed => 'Delivery failed',
            self::Returned => 'Returned', self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning', self::Ready, self::Shipped => 'info',
            self::InTransit, self::OutForDelivery => 'primary', self::Delivered => 'success',
            self::Failed, self::Cancelled => 'danger', self::Returned => 'gray',
        };
    }
}
