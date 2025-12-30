<?php

namespace App\Enums;

enum TenderApprovalStatusEnum: int
{
    case PENDING = 0;  // Default for new tenders
    case APPROVED = 1;
    case REJECTED = 2;

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
}
