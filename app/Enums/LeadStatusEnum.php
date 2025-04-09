<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum LeadStatusEnum: string
{
    use UsefulEnumTrait;

    case Warm = 'wa';

    case Hot = 'ho';

    case Won = 'wo';
    case  Cold = 'co';

    public function getIcon(): string
    {
        return match ($this) {
            self::Warm => '💛',
            self::Won => '✅',
            self::Hot => '🔥',
            self::Cold => '😔',
        };
    }

    public function description(): string
    {
        $text = match ($this) {
            self::Warm => $this->name . ' - Minor Discussions',
            self::Won => $this->name . ' - Onboarding Member',
            self::Hot => $this->name . ' - Very Busy',
            self::Cold => $this->name . ' - Lost',
        };
        return $this->getIcon() . ' ' . $text;
    }
}
