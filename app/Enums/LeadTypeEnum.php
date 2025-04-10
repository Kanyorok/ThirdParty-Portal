<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum LeadTypeEnum: string
{
    use UsefulEnumTrait;

    case Individual = 'i';

    case Company = 'c';

}
