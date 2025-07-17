<?php

namespace App\Enums;

enum ThirdPartyStatusEnum: string
{
    case Active = 'A';
    case Suspended = 'S';
    case Blacklisted = 'B';
    case Inactive = 'I';
    case Terminated = 'T';
}
