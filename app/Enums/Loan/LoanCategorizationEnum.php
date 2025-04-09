<?php

namespace App\Enums\Loan;

use App\Traits\UsefulEnumTrait;

enum LoanCategorizationEnum: string
{
    use UsefulEnumTrait;

    case BOSA = '1017';

    case FOSA = '1018';

    case MicroLoans = '0000';

    public function description(): string
    {
        return match ($this) {
            self::BOSA => 'BOSA Loans',
            self::FOSA => 'FOSA Loans',
            self::MicroLoans => 'Micro Loans',
        };
    }
}
