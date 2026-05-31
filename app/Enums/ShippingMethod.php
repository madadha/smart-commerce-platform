<?php

namespace App\Enums;

enum ShippingMethod: string
{
    case HomeDelivery = 'home_delivery';
    case Pickup = 'pickup';
    case Express = 'express';
    case Standard = 'standard';
    case Free = 'free';
    case ExternalCompany = 'external_company';

    public function label(): string
    {
        return match ($this) {
            self::HomeDelivery => 'Home Delivery',
            self::Pickup => 'Pickup',
            self::Express => 'Express Delivery',
            self::Standard => 'Standard Delivery',
            self::Free => 'Free Delivery',
            self::ExternalCompany => 'External Shipping Company',
        };
    }
}