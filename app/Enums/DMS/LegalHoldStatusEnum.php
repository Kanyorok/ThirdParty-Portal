<?php

namespace App\Enums\DMS;

use App\Traits\UsefulEnumTrait;

enum LegalHoldStatusEnum: string
{
    use UsefulEnumTrait;

    case Active = 'A';

    case Canceled = 'C';

    case Released = 'R';
}
