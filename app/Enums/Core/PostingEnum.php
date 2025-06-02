<?php
namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;

enum PostingEnum: string
{
    use UsefulEnumTrait;
    case Posted = 'p';

    case Submitted = 's';

    case Approved = 'a';

    case Draft = 'd';

    public function label(): string
    {
        return match($this) {
            self::Posted => 'Posted',
            self::Draft => 'Draft',
            self::Approved => 'Approved',
            self::Submitted => 'Submited',
        };
    }
    public function badgeColor(): string
    {
        return match($this) {
            self::Posted => 'success',
            self::Draft => 'warning',
            self::Approved => 'success',
            self::Submitted => 'warning',
        };
    }
}

