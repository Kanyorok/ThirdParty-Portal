<?php

namespace App\Enums\Insurance;

use App\Traits\UsefulEnumTrait;

enum InsurancePolicyStatus:string
{
    use UsefulEnumTrait;

    case Proposal = 'P';

    case SubmittedForUnderwriting  = 'S';

    case Issued = 'I';

    case  Rejected = 'R';


    public function label(): string
    {
        return match ($this) {
            self::Proposal => 'Proposal',
            self::SubmittedForUnderwriting => 'Submitted For Under Writting',
            self::Issued => 'Issued',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Proposal => 'warning',
            self::SubmittedForUnderwriting => 'info',
            self::Issued => 'success',
            self::Rejected => 'Danger',
        };
    }
}
