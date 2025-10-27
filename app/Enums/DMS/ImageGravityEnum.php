<?php

namespace App\Enums\DMS;

use App\Traits\UsefulEnumTrait;

enum ImageGravityEnum: string
{
    use UsefulEnumTrait;

    case NorthWest = 'nw';
    case North = 'nn';
    case NorthEast = 'ne';
    case West = 'ww';
    case Center = 'cc';
    case East = 'ee';
    case SouthWest = 'sw';
    case South = 'ss';
    case SouthEast = 'se';

    public function description(): string
    {
        return match ($this) {
            self::NorthWest => 'Top-left corner',
            self::North => 'Top-center edge',
            self::NorthEast => 'Top-right corner',
            self::West => 'Middle-left edge',
            self::Center => 'Exact center',
            self::East => 'Middle-right edge',
            self::SouthWest => 'Bottom-left corner',
            self::South => 'Bottom-center edge',
            self::SouthEast => 'Bottom-right corner',
        };
    }

    public function class(): string
    {
        return match ($this) {
            self::NorthWest => 'text-start mb-4',
            self::North => 'text-center mb-4',
            self::NorthEast => 'text-end mb-4',
            self::West => 'text-start my-2',
            self::Center => 'text-center m-2',
            self::East => 'text-end my-2',
            self::SouthWest => 'text-start mt-4',
            self::South => 'text-center mt-4',
            self::SouthEast => 'text-end mt-4',
        };
    }
}
