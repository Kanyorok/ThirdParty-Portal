<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum InvitationStatusEnum: string {
    use UsefulEnumTrait; 

    case Invited = 'Invited';
    case Accepted = 'Accepted';
    case Declined = 'Declined';
}
