<?php

namespace App\Enums\Insurance;

use App\Traits\UsefulEnumTrait;

enum InsuranceClosureEnum: string
{
    use UsefulEnumTrait;

    case Successful = 'S';

    case Rejected = 'R';

    case Escalated = 'E';


    public function label(): string
    {
        return match ($this) {
            self::Successful => 'Successful Closed',
            self::Rejected => 'Rejected at Closure',
            self::Escalated => 'Escalated',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Successful => 'success',
            self::Rejected => 'damaged',
            self::Escalated => 'warning',
        };
    }
}
