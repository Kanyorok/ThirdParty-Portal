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
}
