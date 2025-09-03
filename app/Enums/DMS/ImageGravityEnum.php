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
}
