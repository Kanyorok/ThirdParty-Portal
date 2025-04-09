<?php

namespace App\Enums\Schedule;

use App\Traits\UsefulEnumTrait;

enum MeetingLocationEnum: string
{
    use UsefulEnumTrait;

    case Online = 'on';
    case Physical = 'ph';
    case Local = 'lo';
}
