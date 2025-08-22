<?php

namespace App\Enums\Insurance;

use App\Traits\UsefulEnumTrait;

enum InsuranceReferralStatus: string
{
    use UsefulEnumTrait;

    case Pending = 'P';

    case Assigned = 'A';

    case Converted = 'C';

    case  Rejected = 'R';


    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Assigned => 'Assigned',
            self::Converted => 'Converted',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Assigned => 'info',
            self::Converted => 'success',
            self::Rejected => 'Secondary',
        };
    }
}
