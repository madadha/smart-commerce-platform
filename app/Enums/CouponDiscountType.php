<?php

namespace App\Enums;

enum CouponDiscountType: string
{
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';
    case FreeShipping = 'free_shipping';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Percentage Discount',
            self::FixedAmount => 'Fixed Amount Discount',
            self::FreeShipping => 'Free Shipping',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Percentage => 'خصم نسبة مئوية',
            self::FixedAmount => 'خصم مبلغ ثابت',
            self::FreeShipping => 'شحن مجاني',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Percentage => 'success',
            self::FixedAmount => 'info',
            self::FreeShipping => 'warning',
        };
    }
}