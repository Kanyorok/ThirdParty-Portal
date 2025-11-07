<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum ScheduleStatusEnum: string
{
    use UsefulEnumTrait;

    case Canceled = 'cc';

    case Scheduled = 'sc';

    case PartialSuccess = 'ps';

    case Success = 'ss';

    public function description(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::PartialSuccess => 'Partial Success',
            self::Success => 'Success',
            self::Canceled => 'Canceled',
        };
    }

    public function colour(bool $past = false): string
    {
        if ($this->value === self::Scheduled->value && $past) {
            return '#ff0000';//red
        }
        return match ($this) {
            self::Canceled => '#708090',//grey
            self::Scheduled => '#4a6edb',//blue
            self::PartialSuccess => '#dbb74b',//yellow
            self::Success => '#14b768',//green
        };
    }

    public function actionable(): bool
    {
        return match ($this) {
            self::Canceled, self::Success => false,
            default => true,
        };
    }

    public function cancelable(): bool
    {
        return match ($this) {
            self::Canceled, self::Success, self::PartialSuccess => false,
            default => true,
        };
    }
}
