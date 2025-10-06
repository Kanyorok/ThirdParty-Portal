<?php

namespace App\Enums\Procurement;

use App\Traits\UsefulEnumTrait;

enum PrequalificationApplicationEnum: string
{
    use UsefulEnumTrait;


    case Submitted = 'S';
    case Reviewed = 'V';
    case Prequalified = 'P';
    case Approved = 'A';
    case Rejected = 'R';

    public function getLabel(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Reviewed => 'Reviewed',
            self::Prequalified => 'Prequalified',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Submitted => 'bg-yellow-100 text-yellow-800',
            self::Reviewed => 'bg-blue-100 text-blue-800',
            self::Prequalified => 'bg-teal-100 text-teal-800',
            self::Approved => 'bg-green-100 text-green-800',
            self::Rejected => 'bg-red-100 text-red-800',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Submitted => 'fa-solid fa-hourglass-start',
            self::Reviewed => 'fa-solid fa-search',
            self::Prequalified => 'fa-solid fa-badge-check',
            self::Approved => 'fa-solid fa-check-circle',
            self::Rejected => 'fa-solid fa-times-circle',
        };
    }
}
