<?php

namespace App\Enums\Core;

use App\Exceptions\ErroredException;
use App\Traits\UsefulEnumTrait;
use Illuminate\Support\Collection;


enum ComparisonOperatorsEnum: string
{
    use UsefulEnumTrait;

    case EqualTo = 'et';
    case GreaterThan = 'gt';
    case GreaterThanOrEqual = 'ge';
    case LessThan = 'lt';
    case LessThanOrEqual = 'le';
    case NotEqual = 'ne';//<>

    case Between = 'bw';

    case In = 'in';

    /**
     * @throws ErroredException
     */
    public function symbol(): string
    {
        return match ($this) {
            self::EqualTo => '=',
            self::GreaterThan => '>',
            self::GreaterThanOrEqual => '>=',
            self::LessThan => '<',
            self::LessThanOrEqual => '<=',
            self::NotEqual => '!=',
            default => throw new ErroredException("Comparison operators not supported"),
        };

    }

    public function isBasic(): bool
    {
        return in_array($this, self::basic()->toArray(), true);
    }

    public static function basic(): Collection
    {
        return collect([self::EqualTo, self::GreaterThan, self::GreaterThanOrEqual, self::LessThan, self::LessThanOrEqual, self::NotEqual]);
    }

}
