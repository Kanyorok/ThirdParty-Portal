<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;

enum PostingEnum: string
{
    use UsefulEnumTrait;
    case Posted = 'p';

    case Draft = 'd';

    public function label(): string
    {
        return match($this) {
            self::Posted => 'Posted',
            self::Draft => 'Draft',
        };
    }
    public function badgeColor(): string
    {
        return match($this) {
            self::Posted => 'success',
            self::Draft => 'warning',
        };
    }
}

