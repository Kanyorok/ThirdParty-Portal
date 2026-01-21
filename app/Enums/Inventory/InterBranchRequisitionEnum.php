<?php

namespace App\Enums\Inventory;

use App\Traits\UsefulEnumTrait;

enum InterBranchRequisitionEnum: string
{
    use UsefulEnumTrait;

    case Approved = 'Ap';

    case Rejected = 'Re';

    case Pending = 'P';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Rejected => 'Rejected',
            self::Approved => 'Approved',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
