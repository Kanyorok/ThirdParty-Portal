<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TenderStatusEnum: string
{
    use UsefulEnumTrait;

    case Draft = 'dr';
    case Published = 'pb';
    case Closed = 'cl';

    public function colorClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-primary',
            self::Published => 'bg-warning text-dark',
            self::Closed => 'bg-gray',
        };
    }

    public function displayName(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Closed => 'Closed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-info-soft text-info',
            self::Published => 'bg-success-soft text-success',
            self::Closed => 'bg-secondary-soft text-secondary',
            default => 'bg-light text-dark',
        };
    }
}
