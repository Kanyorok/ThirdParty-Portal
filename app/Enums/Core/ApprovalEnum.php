<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;

enum ApprovalEnum: string
{
    use UsefulEnumTrait;

    case Approved = 'A';

    case Rejected = 'R';

    case Pending = 'P';

    case Submitted = 'S';

    case Draft='dr';

    case Posted='ps';
    case Cancelled = 'Ca';

    case Completed = 'Co';

    case Ongoing = 'Og';

    case Scheduled = 'Sc';
    case Available = 'av';
    case OnTrip = 'ot';
    case AssignedTrip = 'at';



    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Pending => 'Pending',
            self::Submitted => 'Submitted for Approval',
            self::Draft=>'Draft',
            self::Posted=>'Posted',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
            self::Ongoing => 'Ongoing',
            self::Scheduled => 'Scheduled',
            self::Available => 'Available',
            self::OnTrip => 'On Trip',
            self::AssignedTrip => 'Assigned Trip',

        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Approved => 'success',
            self::Pending => 'info',
            self::Rejected => 'danger',
            self::Submitted => 'warning',
            self::Draft=>'secondary',
            self::Posted=>'success',
            self::Cancelled => 'secondary',
            self::Completed => 'primary',
            self::Ongoing => 'info',
            self::Scheduled => 'secondary',
            self::Available => 'success',
            self::OnTrip => 'warning',
            self::AssignedTrip => 'info',

        };
    }
}

