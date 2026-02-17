<?php

namespace App\Enums\Procurement;

use App\Traits\UsefulEnumTrait;

enum DepartmentNeedsEnum: string
{
    use UsefulEnumTrait;

    case Approved = 'a';

    case Rejected = 'r';

    case Pending = 'p';

    case Submitted = 's';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Rejected => 'Rejected',
            self::Approved => 'Approved',
            self::Submitted => 'Submitted for Approval',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Submitted => 'info',
        };
    }
}
