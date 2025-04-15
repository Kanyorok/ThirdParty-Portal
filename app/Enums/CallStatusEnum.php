<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;
use Illuminate\Support\Collection;

enum CallStatusEnum: string
{
    use UsefulEnumTrait;

    case SuccessDiscussion = 'sd';

    case SuccessReschedule = 'sr';

    case SuccessOngoing = 'so';

    case PhoneOff = 'po';

    case NotReceived = 'nt';

    public function description(): string
    {
        return match ($this) {
            self::SuccessDiscussion => 'Success with discussion',
            self::SuccessReschedule => 'Success and reschedule',
            self::SuccessOngoing => 'Ongoing',
            self::PhoneOff => 'Phone off',
            self::NotReceived => 'Not received'
        };
    }

    public static function unreachable(): Collection
    {
        return  collect([self::PhoneOff, self::NotReceived]);
    }
}
