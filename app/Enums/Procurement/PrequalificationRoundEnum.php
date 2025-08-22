<?php

namespace App\Enums\Procurement;

enum PrequalificationRoundEnum: string
{
    case Draft = 'D';
    case Open     = 'O';
    case Closed    = 'CL';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft Round',
            self::Open      => 'Open for Application',
            self::Closed    => 'Closed - Applications not Allowed',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-secondary',
            self::Open      => 'bg-success',
            self::Closed    => 'bg-warning text-dark',
        };
    }
}
