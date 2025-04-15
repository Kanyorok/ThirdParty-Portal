<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum ItemTypeEnum: string
{
    use UsefulEnumTrait;

    case FinancialCapabilities = 'cfc';//todo confirm state
    case CustomerServicePerception = 'csp';
    case Strength = 'cst';
    case Weaknesses = 'cwe';
}
