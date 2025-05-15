<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TenderStatusEnum: string {
    use UsefulEnumTrait; 

    case Draft = 'dr';
    case Published = 'pb';
    case Closed = 'cl';
}
