<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Served = 'served';
    case Paid = 'paid';
    case Confirmed = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Preparing => 'Preparing',
            self::Ready => 'Ready',
            self::Served => 'Served',
            self::Paid => 'Paid',
            self::Confirmed => 'Confirmed',
        };
    }
}
