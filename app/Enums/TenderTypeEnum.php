<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TenderTypeEnum: string{
    use UsefulEnumTrait;

    case Open = 'Open';
    case Restricted = 'Restricted';

    public static function values(): array{
        return array_column(self::cases(), 'values');
    }

    public function requiresVendorApproval(): bool {
        return $this === self.Restricted;
    }
}
