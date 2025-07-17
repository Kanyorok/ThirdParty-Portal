<?php

namespace App\Enums;

enum ThirdPartyApprovalStatusEnum: string
{
    case Pending = 'P';
    case Approved = 'A';
    case Rejected = 'R';
}
