<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum BasicStatusEnum: string
{
    use UsefulEnumTrait;

    case Draft = 'df';
    case Active = 'aa';
    case Archived = 'ar';

    public function description(): string
    {
        return match ($this) {
            self::Active => 'ACTIVE',
            self::Archived => 'ARCHIVED',
            self::Draft => 'DRAFT',
        };
    }
}
