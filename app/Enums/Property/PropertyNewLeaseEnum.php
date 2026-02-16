<?php

namespace App\Enums\Property;

use App\Traits\UsefulEnumTrait;

enum PropertyNewLeaseEnum: string
{
    use UsefulEnumTrait;

    case OfferLetter = 'o';

    case New = 'n';

    case Renew = 'r';

    case Terminate = 't';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Renew => 'Renew',
            self::Terminate => 'Terminate',
            self::OfferLetter => 'Offer Letter',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::New => 'success',
            self::Renew => 'info',
            self::Terminate => 'danger',
            self::OfferLetter => 'primary',
        };
    }
}
