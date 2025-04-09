<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum MeetingStatusEnum: string
{
    use UsefulEnumTrait;

    case Canceled = 'cc';

    case Scheduled = 'sc';

    case Ongoing = 'so';

    case Completed = 'ss';
}
