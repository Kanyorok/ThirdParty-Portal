<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TicketSourceEnum: string
{
    use UsefulEnumTrait;

    case Email = 'email';

    case Phone = 'phone';

    case Facebook = 'facebook';

    case Twitter = 'twitter';

    case WalkIn = 'walk-in';

    case Website = 'website';

    case Channels = 'channels';
}
