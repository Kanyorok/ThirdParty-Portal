<?php

namespace App\Enums;

enum TenderAwardStatusEnum: string
{
    case DRAFT = 'Draft';
    case PENDING = 'Pending';
    case SUBMITTED = 'Submitted for Approval';
    case UNDER_REVIEW = 'Under Review';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case CANCELLED = 'Cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Approval',
            self::SUBMITTED => 'Submitted for Approval',
            self::UNDER_REVIEW => 'Under Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::PENDING => 'warning',
            self::SUBMITTED => 'info',
            self::UNDER_REVIEW => 'primary',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED => 'dark',
        };
    }
}