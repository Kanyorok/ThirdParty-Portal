<?php

namespace App\Enums;

enum ThirdPartyApprovalStatusEnum: string
{
    case Pending = 'P';
    case Approved = 'A';
    case Rejected = 'R';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::Pending => 'pending',
            self::Approved => 'approved',
            self::Rejected => 'rejected',
        };
    }
}
