<?php

namespace App\Enums\Property;

use App\Traits\UsefulEnumTrait;

enum TenantClearanceEnum: string
{
    use UsefulEnumTrait;

    case Pending = 'p';

    case Cleared = 'c';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Cleared => 'Cleared',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Cleared => 'success',
        };
    }
}
