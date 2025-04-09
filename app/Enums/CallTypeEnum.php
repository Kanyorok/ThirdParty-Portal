<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum CallTypeEnum: string
{
    use UsefulEnumTrait;

    case Incoming = 'in';
    case Outgoing = 'ou';
}
