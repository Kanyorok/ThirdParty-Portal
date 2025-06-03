<?php

namespace App\Enums\Procurement;

use App\Traits\UsefulEnumTrait;

enum SchedulePlanEnum: string
{
    use UsefulEnumTrait;

    case FullyScheduled = 'f';

    case PartiallyScheduled = 'p';

    case NotScheduled = 'n';

    public function label(): string
    {
        return match ($this) {
            self::FullyScheduled => 'Fully Scheduled',
            self::PartiallyScheduled => 'Partially Scheduled',
            self::NotScheduled => 'Not Scheduled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::FullyScheduled => 'success',
            self::PartiallyScheduled => 'warning',
            self::NotScheduled => 'danger',
        };
    }
}
