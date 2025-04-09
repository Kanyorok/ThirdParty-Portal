<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum EmailPriorityEnum: string
{
    use UsefulEnumTrait;

    case Important = 'i';

    case Normal = 'n';

    case Low = 'l';

    public function intPriority(): int
    {
        return match ($this->value) {
            self::Important->value => 1,
            self::Normal->value => 3,
            self::Low->value => 4,
        };
    }
}
