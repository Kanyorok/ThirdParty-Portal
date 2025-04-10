<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TicketStatusEnum: string
{
    use UsefulEnumTrait;

    case Active = 'A';

    case Cancelled = 'C';

    case Resolved = 'R';

    case Approval = 'P';
}
