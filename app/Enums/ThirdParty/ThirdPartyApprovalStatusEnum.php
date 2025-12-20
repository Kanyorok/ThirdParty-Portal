<?php

namespace App\Enums\ThirdParty;

enum ThirdPartyApprovalStatusEnum: string
{
    case Pending = 'P';
    case Approved = 'A';
    case Rejected = 'R';
    case Submitted = 'U';
    case Suspended = 'S';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Submitted => 'Submitted',
            self::Suspended => 'Suspended',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::Pending => 'pending',
            self::Approved => 'approved',
            self::Rejected => 'rejected',
            self::Submitted => 'info',
            self::Suspended => 'rejected', // Or a darker red/grey? Using rejected logic for now. User said "cannot login", "cannot be used".
        };
    }
}
