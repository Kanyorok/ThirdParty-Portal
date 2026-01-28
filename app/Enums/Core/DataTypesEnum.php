<?php

namespace App\Enums\Core;

use App\Services\DMS\Files\FileProperties;
use App\Traits\UsefulEnumTrait;
use Carbon\Carbon;
use Exception;

enum DataTypesEnum: string
{
    use UsefulEnumTrait;

    case DateTime = 'dt';
    case String = 'st';
    case Time = 'tm';
    case Integer = 'it';

    case Float = 'ft';

    case Boolean = 'bo';

    public function isValid(mixed $value, mixed $format = null): bool
    {
        return match ($this) {
            self::DateTime => (Carbon::createFromFormat($format ?? self::dateTimeFormat(), $value) instanceof Carbon),
            self::Float, self::Integer => is_numeric($value),
            self::Boolean => is_bool($value),
            self::String => is_string($value),
            self::Time => (Carbon::createFromFormat($format ?? self::timeFormat(), $value) instanceof Carbon),
            default => false,
        };
    }

    public function val(mixed $value, mixed $format = null): string
    {
        try {
            return match ($this) {
                self::DateTime => Carbon::createFromFormat($format ?? self::dateTimeFormat(), $value)?->format('Y-m-d H:i'),
                self::Float => number_format($value, 2),
                self::Integer => number_format($value),
                self::Boolean => ($value) ? 'yes' : 'no',
                self::String => (string)$value,
                self::Time => Carbon::createFromFormat($format ?? self::timeFormat(), $value)?->format('H:i'),
                default => false,
            };
        } catch (Exception $e) {
            // Log error and return false for invalid format
            \Log::error('DataTypesEnum format validation failed', [
                'value' => $value,
                'format' => $format,
                'type' => $this->value,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public static function timeFormat(): string
    {
        return FileProperties::TIME_FORMAT;
    }

    public static function dateTimeFormat(): string
    {
        return 'Y-m-d H:i';
    }
}
