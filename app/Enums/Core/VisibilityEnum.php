<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;

enum VisibilityEnum: string
{
    use UsefulEnumTrait;

    case Public = 'pub';

    case Private = 'pri';

    public function description(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Private => 'Private',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Public => '<i class="fas fa-globe text-primary" title="Public"></i>',
            self::Private => '<i class="fas fa-lock text-secondary" title="Private"></i>',
        };
    }
}
