<?php

namespace App\Enums\Inventory;

use App\Traits\UsefulEnumTrait;

enum Transfers: string
{
    use UsefulEnumTrait;


    case Pending = 'P';

    case InTransit = 'it';

    case Delivered = 'de';

    case Rejected = 'Re';

    case Approved = 'Ap';

    case UnderReview = 'rv';

    //case UnderRepair = 'ur';

    case AwaitingReview = 'ar';

    case Disposed = 'di';

    case Returned = 'rt';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InTransit => 'In Transit',
            self::Delivered => 'Delivered',
            self::Rejected => 'Rejected',
            self::Approved => 'Approved',
            self::UnderReview => 'Under Review',
            self::Disposed => 'Disposed',
            self::Returned => 'Returned',
            self::AwaitingReview => 'Awaiting Review',
            //self::UnderRepair => 'Under Repair',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::InTransit => 'primary',
            self::Delivered => 'success',
            self::Rejected => 'danger',
            self::Approved => 'success',
            self::UnderReview => 'info',
            self::Disposed => 'danger',
            self::Returned => 'secondary',
            self::AwaitingReview => 'info',

            //self::UnderRepair => 'warning',
        };
    }
}
