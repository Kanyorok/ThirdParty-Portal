<?php

namespace App\Enums\ThirdParty;

enum ThirdPartyTypeEnum: string
{
    case Supplier = 'S';
    case Tenant = 'T';
    case Customer = 'C';

    public function label(): string
    {
        return match ($this) {
            self::Supplier => 'Supplier',
            self::Tenant => 'Tenant',
            self::Customer => 'Customer',
        };
    }
}
