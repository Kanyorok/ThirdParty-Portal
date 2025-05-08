<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TenderStatusEnum: string{
    use UsefulEnumTrait; 

    case Draft = 'Draft';
    case Published = 'Published';
    case Closed = 'Closed';

    public function label(): string{
        return match($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Closed => 'Closed',
        };
    }

    public function canTransitionTo(TenderStatus $status): bool{
        return match ($this) {
            self::Draft => in_array($status, [self::Published, self::Closed]),
            default => false,
        };
    }
}
