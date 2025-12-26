<?php

namespace App\Enums;

enum OrderStatus: int
{
    case Open = 1;
    case Filled = 2;
    case Cancelled = 3;

    public function label(): string
    {
        return match($this) {
            self::Open => 'Open',
            self::Filled => 'Filled',
            self::Cancelled => 'Cancelled',
        };
    }
}
