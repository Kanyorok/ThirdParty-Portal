<?php

namespace App\Enums\Procurement;

use App\Traits\UsefulEnumTrait;

enum PrequalificationApplicationEnum: string
{
    use UsefulEnumTrait;


    case Submitted = 'S';
    case Approved = 'A';
    case Rejected = 'R';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }
}
