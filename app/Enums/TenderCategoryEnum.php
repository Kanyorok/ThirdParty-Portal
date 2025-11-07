<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TenderCategoryEnum: string
{
    use UsefulEnumTrait;

    case Goods = 'G';
    case Services = 'S';
    case Works = 'W';

    public static function values(): array
    {
        return array_column(self::cases(), 'values');
    }

    public function displayName(): string
    {
        return match ($this) {
            self::Goods => 'Goods',
            self::Services => 'Services',
            self::Works => 'Works',
        };
    }
}
