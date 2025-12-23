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

    case Deferred = 'Df';
    case COMMENTED = 'Cm';
    case RETURNED = 'Dr';
    case Pending = 'pe';
    case UnderReview = 'rv';
    case UnderRepair = 'ur';
    case Disposed = 'di';
    case InTransit = 'it';
    case Delivered = 'de';
    case Scheduled = 'SC';
    case Approved = 'AP';
    case Rejected = 'RE';
    case Canceled = 'CA';
    case Ongoing = 'OG';
    case Acknowledged = 'AC';
    case Completed = 'CO';
    case RejectedDelete = 'rd';
    case RejectedCancel = 'rc';
    case RejectReturn = 'rr';
    case Available = 'av';
    case OnTrip = 'ot';
    case AssignedTrip = 'at';
}
