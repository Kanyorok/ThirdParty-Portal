<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum GenderEnum: string
{
    use UsefulEnumTrait;

    case Male = 'm';

    case Female = 'f';

    case Other = 'o';

    public function getBRCode(): string
    {
        return match ($this) {
            self::Male => 'M',
            self::Female => 'F',
            default => 'U',
        };
    }
}
