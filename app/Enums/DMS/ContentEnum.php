<?php

namespace App\Enums\DMS;

use App\Traits\UsefulEnumTrait;

enum ContentEnum: string
{
    use UsefulEnumTrait;

    case Title = 'tl';
    case Body = 'bd';

    //case Attachment = 'at';

    public function description(): string
    {
        return match ($this) {
            self::Title => 'Document Title',
            self::Body => 'Document Content',
        };
    }
}
