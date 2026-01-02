<?php

namespace App\Enums;

enum TenderAwardStatusEnum: string
{
    case DRAFT = 'Draft';
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case CANCELLED = 'Cancelled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Approval',
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
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED => 'dark',
        };
    }
}