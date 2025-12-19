<?php

namespace App\Enums;

enum TenderApprovalStatusEnum: string
{
   case PENDING = 'P';  // Use string '0', not integer 0
    case APPROVED = 'Ap';
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
