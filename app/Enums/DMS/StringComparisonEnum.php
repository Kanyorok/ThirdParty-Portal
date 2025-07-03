<?php

namespace App\Enums\DMS;

use App\Traits\UsefulEnumTrait;

enum StringComparisonEnum: string
{
    use UsefulEnumTrait;

    case Exact = 'eq';
    case NotExact = 'ne';
    case Contains = 'cn';
    case NotContains = 'nc';
    case StartsWith = 'sw';
    case EndsWith = 'en';

    public function description(): string
    {
        return match ($this) {
            self::Exact => 'Equal To',
            self::NotExact => 'Not Equal To',
            self::Contains => 'Contains',
            self::NotContains => 'Not Contains',
            self::StartsWith => 'Starts With',
            self::EndsWith => 'Ends With',
        };
    }
}
