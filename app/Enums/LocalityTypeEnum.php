<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum LocalityTypeEnum: string
{
    use UsefulEnumTrait;

    case County = 'cc';

    case City = 'ci';

    public function hasParent(): bool
    {
        return ($this->value === self::City->value);
    }
}
