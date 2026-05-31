<?php

namespace App\Enums;

enum ShippingMethodType: string
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

    public function labelAr(): string
    {
        return match ($this) {
            self::HomeDelivery => 'توصيل للبيت',
            self::Pickup => 'استلام ذاتي',
            self::Express => 'توصيل سريع',
            self::Standard => 'توصيل عادي',
            self::Free => 'توصيل مجاني',
            self::ExternalCompany => 'شركة توصيل خارجية',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::HomeDelivery => 'info',
            self::Pickup => 'gray',
            self::Express => 'warning',
            self::Standard => 'primary',
            self::Free => 'success',
            self::ExternalCompany => 'purple',
        };
    }

    public function requiresAddress(): bool
    {
        return match ($this) {
            self::Pickup => false,
            default => true,
        };
    }
}