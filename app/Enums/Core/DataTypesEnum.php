<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;
use Carbon\Carbon;

enum DataTypesEnum: string
{
    use UsefulEnumTrait;

    case DateTime = 'dt';
    case String = 'st';
    case Integer = 'it';

    case Float = 'ft';

    case Boolean = 'bo';

    public function isValid(mixed $value): bool
    {
        return match ($this) {
            self::DateTime => (Carbon::createFromFormat(self::dateTimeFormat(), $value) instanceof Carbon),
            self::Float, self::Integer => is_numeric($value),
            self::Boolean => is_bool($value),
            self::String => is_string($value),
            default => false,
        };
    }

    public static function dateTimeFormat(): string
    {
        return 'Y-m-d H:i';
    }
}
