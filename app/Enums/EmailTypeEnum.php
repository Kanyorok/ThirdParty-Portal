<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum EmailTypeEnum: string
{
    use UsefulEnumTrait;

    case Incoming = "i";

    case Outgoing = "o";
}
