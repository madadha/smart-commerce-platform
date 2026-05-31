<?php

namespace App\Enums;

enum ProductType: string
{
    case Physical = 'physical';
    case DigitalCard = 'digital_card';
    case DigitalFile = 'digital_file';
    case Service = 'service';
    case Subscription = 'subscription';
    case Bundle = 'bundle';

    public function label(): string
    {
        return match ($this) {
            self::Physical => 'Physical Product',
            self::DigitalCard => 'Digital Card',
            self::DigitalFile => 'Digital File',
            self::Service => 'Service',
            self::Subscription => 'Subscription',
            self::Bundle => 'Bundle',
        };
    }

    public function requiresShipping(): bool
    {
        return match ($this) {
            self::Physical, self::Bundle => true,
            self::DigitalCard, self::DigitalFile, self::Service, self::Subscription => false,
        };
    }
}