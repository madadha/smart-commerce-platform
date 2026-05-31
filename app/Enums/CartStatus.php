<?php

namespace App\Enums;

enum CartStatus: string
{
    case Active = 'active';
    case Converted = 'converted';
    case Abandoned = 'abandoned';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Converted => 'Converted',
            self::Abandoned => 'Abandoned',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Converted => 'info',
            self::Abandoned => 'warning',
            self::Cancelled => 'danger',
        };
    }
}