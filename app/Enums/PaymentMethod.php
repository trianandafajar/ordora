<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Xendit = 'xendit';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Xendit => 'Xendit',
        };
    }
}
