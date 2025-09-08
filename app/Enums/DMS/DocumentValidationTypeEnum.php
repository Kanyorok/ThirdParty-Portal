<?php

namespace App\Enums\DMS;

use App\Traits\UsefulEnumTrait;

enum DocumentValidationTypeEnum: string
{
    use UsefulEnumTrait;

    case ClientOnboarding = 'CLREG';
    case LoanApplication = 'LNAPP';
}
