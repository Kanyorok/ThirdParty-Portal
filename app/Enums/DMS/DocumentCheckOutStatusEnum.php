<?php

namespace App\Enums\DMS;

use App\Traits\UsefulEnumTrait;

enum DocumentCheckOutStatusEnum: string
{
    use UsefulEnumTrait;

    case CheckOut = 'out';
    case CheckIn = 'cin';

    case Canceled = 'can';
}
