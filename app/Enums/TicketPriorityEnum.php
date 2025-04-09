<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TicketPriorityEnum: string
{
    use UsefulEnumTrait;

    case Urgent = 'U';

    case Normal = 'N';

    case Low = 'L';
}
