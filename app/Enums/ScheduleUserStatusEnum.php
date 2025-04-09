<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum ScheduleUserStatusEnum: string
{
    use UsefulEnumTrait;

    case Requested = 'rq';

    case Accepted = 'ac';

    case Rejected = 'rj';
}
