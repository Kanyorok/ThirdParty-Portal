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
            self::Suspended => 'rejected',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->getAllowedTransitions());
    }

    public function getAllowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Approved, self::Rejected],
            self::Approved => [self::Rejected],
            self::Rejected => [self::Pending],
        };
    }

    public function isActive(): bool
    {
        return $this === self::Approved;
    }

    public function canCreateTransactions(): bool
    {
        return $this === self::Approved;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
