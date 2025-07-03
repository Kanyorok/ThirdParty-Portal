<?php

namespace App\Enums\Procurement;

enum PrequalificationPeriodEnum: string
{
    case Draft = 'd';

    case Open = 'o';

    case Closed = 'c';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Open => 'Open',
            self::Closed => 'Closed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'info',
            self::Open => 'success',
            self::Closed => 'danger',
        };
    }

}

