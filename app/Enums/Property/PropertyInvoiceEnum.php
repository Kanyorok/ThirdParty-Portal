<?php

namespace App\Enums\Property;

use App\Traits\UsefulEnumTrait;

enum PropertyInvoiceEnum: string
{
    use UsefulEnumTrait;

    case FullyPaid = 'F';

    case Pending = 'P';

    case PartialPaid = 'N';


    public function label(): string
    {
        return match ($this) {
            self::FullyPaid => 'Paid',
            self::Pending => 'Pending',
            self::PartialPaid => 'PartialPaid',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::FullyPaid => 'success',
            self::Pending => 'danger',
            self::PartialPaid => 'warning',
        };
    }
}
