<?php

namespace App\Traits;

use App\Exceptions\ErroredException;
use Illuminate\Support\Collection;
use Throwable;

trait UsefulEnumTrait
{
    public static function keys(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::keys(), self::values());
    }

    public static function getAll(): Collection
    {
        return collect(self::cases());

    }

    /**
     * @throws ErroredException
     */
    public static function valueFromName(string $name): self
    {
        try {
            return constant("self::$name");
        } catch (Throwable) {
        }

        throw new ErroredException('Unknown type');
    }


    /**
     * @throws ErroredException
     */
    public static function fromValue(string $value): self
    {
        try {
            return self::from($value);
        } catch (Throwable|\ErrorException|\Exception) {
        }

        throw new ErroredException('Unknown type');
    }
}
