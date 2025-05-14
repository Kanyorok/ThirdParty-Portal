<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TenderCategoryEnum: string {
    use UsefulEnumTrait;

    case Goods = 'gd';
    case Services = 'sv';
    case Works = 'wk';
}
