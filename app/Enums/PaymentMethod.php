<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Xendit = 'xendit';
}
