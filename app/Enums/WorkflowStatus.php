<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum WorkflowStatus: string
{
    use UsefulEnumTrait;

    case Submitted = 'su';
    case Accepted = 'ac';
    case APPROVED = 'Ap';
    case REJECTED = 'Re';
    case COMMENTED = 'Cm';


    case RejectedDelete = 'rd';

    case RejectedCancel = 'rc';

    case RejectReturn = 'rr';
}
