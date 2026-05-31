<?php

namespace App\Enums;

enum CustomerType: string
{
    case Regular = 'regular';
    case Reseller = 'reseller';
    case Vip = 'vip';
    case Company = 'company';

    public function label(): string
    {
        return match ($this) {
            self::Regular => 'Regular Customer',
            self::Reseller => 'Reseller',
            self::Vip => 'VIP Customer',
            self::Company => 'Company',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Regular => 'gray',
            self::Reseller => 'info',
            self::Vip => 'warning',
            self::Company => 'success',
        };
    }
}