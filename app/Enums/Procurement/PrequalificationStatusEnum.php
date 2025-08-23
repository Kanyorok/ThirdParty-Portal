<?php

namespace App\Enums\Procurement;

enum PrequalificationStatusEnum: string
{
    case Draft = 'D';
    case Submitted = 'S';
    case UnderReview = 'U';
    case ReturnedForCorrection = 'C';
    case Approved = 'A';
    case Rejected = 'R';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::UnderReview => "Under Review",
            self::Approved => 'Approved',
            self::ReturnedForCorrection => "Needs Correction",
            self::Rejected => 'Rejected',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::UnderReview => 'indigo',
            self::Approved => 'green',
            self::ReturnedForCorrection => 'orange',
            self::Rejected => 'red',
        };
    }

    public function canTransition(self $next): string
    {
        return match ($this) {
            self::Draft => in_array($next, [self::Submitted]),
            self::Submitted => in_array($next, [self::UnderReview, self::ReturnedForCorrection]),
            self::UnderReview => in_array($next, [self::Approved, self::Rejected, self::ReturnedForCorrection]),
            self::ReturnedForCorrection => in_array($next, [self::Submitted]),
            self::Approved, self::Rejected => false, // final states
        };
    }
}
