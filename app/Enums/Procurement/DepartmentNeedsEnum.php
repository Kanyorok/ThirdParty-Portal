<?php

namespace App\Enums\Procurement;

use App\Traits\UsefulEnumTrait;

enum DepartmentNeedsEnum: string
{
    use UsefulEnumTrait;

    case Approved = 'a';

    case Pending = 'p';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
        };
    }
}
