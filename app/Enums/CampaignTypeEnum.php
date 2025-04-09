<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum CampaignTypeEnum: string
{
    use UsefulEnumTrait;

    case SMS = 's';

    case Email = 'e';
}
