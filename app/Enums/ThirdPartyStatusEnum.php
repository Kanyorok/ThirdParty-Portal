<?php

namespace App\Enums;

enum ThirdPartyStatusEnum: string
{
    case Active = 'A';
    case Suspended = 'S';
    case Blacklisted = 'B';
    case Inactive = 'I';
    case Terminated = 'T';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Suspended => 'Suspended',
            self::Blacklisted => 'Blacklisted',
            self::Inactive => 'Inactive',
            self::Terminated => 'Terminated',
        };
    }
}
