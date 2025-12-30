<?php

namespace App\Enums;

enum TaxPayerTypeEnum: string
{
    case Individual = 'INDV';
    case NonIndividual = 'NINV';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::NonIndividual => 'Non-Individual - Company',
        };
    }
}
