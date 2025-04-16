<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum MarketingListEnum: string
{
    use UsefulEnumTrait;

    case Static = 'st';

    case Dynamic = 'dy';
}
