<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TenderTypeEnum: string
{
    use UsefulEnumTrait;

    case Open = 'op';
    case Restricted = 'rs';

    public function displayName(): string
    {
        return match ($this) {
            self::Open => 'Open Tender',
            self::Restricted => 'Restricted Tender',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'bg-info-soft text-info',
            self::Restricted => 'bg-success-soft text-success',
            default => 'bg-light text-dark',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'info',
            self::Published => 'success',
            self::Closed => 'secondary',
        };
    }
}
