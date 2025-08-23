<?php

namespace App\Enums;

enum ThirdPartyTypeEnum: string
{
    case Supplier = 'S';
    case Tenant = 'T';

    public function label(): string
    {
        return match ($this) {
            self::Supplier => 'Supplier',
            self::Tenant => 'Tenant',
        };
    }
}
