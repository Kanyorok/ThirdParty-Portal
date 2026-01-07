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


    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Pending => 'Pending',
            self::Submitted => 'Submitted for Approval',
            self::Draft=>'Draft',
            self::Posted=>'Posted',
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
        };
    }
}

