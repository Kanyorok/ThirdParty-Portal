<?php

namespace App\Enums;

enum BusinessTypeEnum: string
{
    case SoleProprietorship = 'Sole';
    case Partnership = 'Partnership';
    case Corporation = 'Corporation';
    case LimitedLiabilityCompany = 'LLC';
    case NonProfitOrganization = 'NGO';

    public function label(): string
    {
        return match ($this) {
            self::SoleProprietorship => 'Sole Proprietorship',
            self::Partnership => 'Partnership',
            self::Corporation => 'Corporation',
            self::LimitedLiabilityCompany => 'Limited Liability Company',
            self::NonProfitOrganization => 'Non-Profit Organization',
        };
    }
}
