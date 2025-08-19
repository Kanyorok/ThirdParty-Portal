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
    case RETURNED = 'Dr';
    

    case Pending = 'pe';

    case UnderReview = 'rv';

    case UnderRepair = 'ur';

    case Disposed = 'di';

    case InTransit = 'it';

    case Delivered = 'de';


    case RejectedDelete = 'rd';

    case RejectedCancel = 'rc';

    case RejectReturn = 'rr';
}
