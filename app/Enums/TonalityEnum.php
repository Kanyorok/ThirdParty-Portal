<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TonalityEnum: string
{
    use UsefulEnumTrait;

    case Neutral = 'ne';

    case Positive = 'po';

    case Negative = 'ng';

    case Unknown = 'un';
}
