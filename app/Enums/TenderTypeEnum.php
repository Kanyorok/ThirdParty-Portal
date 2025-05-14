<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum TenderTypeEnum: string {
    use UsefulEnumTrait;

    case Open = 'op';
    case Restricted = 'rs';

    public function displayName(): string
    {
        return match($this) {
            self::Open => 'Open Tender',
            self::Restricted => 'Restricted Tender',
        };
    }

    public function codePrefix(): string
    {
        return match($this) {
            self::Open => 'OPT-',
            self::Restricted => 'RST-',
        };
    }
}
