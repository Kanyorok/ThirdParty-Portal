<?php

namespace App\Enums;

enum TenderApprovalStatusEnum: string
{
    case PENDING = 'P';  // Default for new tenders
    case APPROVED = 'A';
    case REJECTED = 'R';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
}
