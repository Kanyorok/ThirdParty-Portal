<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum CampaignStatusEnum: string
{
    use UsefulEnumTrait;

    case Approval = 'a';

    case Draft = 'd';

    case Sent = 's';

    case Failed = 'f';

    case Processing = 'r';

    case Sending = 'p';
}
