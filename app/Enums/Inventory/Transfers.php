<?php

namespace App\Enums\Inventory;

use App\Traits\UsefulEnumTrait;

enum Transfers: string
{
    use UsefulEnumTrait;


    case Pending = 'pe';

    case InTransit = 'it';

    case Delivered = 'de';

    case Rejected = 'Re';

    case Approved = 'Ap';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InTransit => 'In Transit',
            self::Delivered => 'Delivered',
            self::Rejected => 'Rejected',
            self::Approved => 'Approved',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::InTransit => 'primary',
            self::Delivered => 'success',
            self::Rejected => 'danger',
            self::Approved => 'success',
        };
    }
}
