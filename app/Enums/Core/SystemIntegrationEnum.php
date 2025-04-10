<?php

namespace App\Enums\Core;

enum SystemIntegrationEnum: string
{
    case Website = 'website';

    case Channels = 'channels';
    case PBX = 'pbx-3cx';

}
