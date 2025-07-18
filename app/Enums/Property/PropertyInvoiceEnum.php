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
            self::FullyPaid => 'Fully Paid',
            self::Pending => 'Pending Payment',
            self::PartialPaid => 'Partially Paid',
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
