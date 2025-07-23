<?php

namespace App\Enums;

enum BusinessTypeEnum: string
{
    case SoleProprietorship = 'Sole';
    case Partnership = 'Partnership';
    case Corporation = 'Corporation';
    case LimitedLiabilityCompany = 'LLC';
    case NonProfitOrganization = 'NGO';
}
