<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TenderCategoryEnum: string{
    use UsefulEnumTrait; 

    case Goods = 'Goods';
    case Services = 'Services';
    case Works = 'Works';

    public static function values(): array{
        return array_column(self::cases(), 'values');
    }
}
