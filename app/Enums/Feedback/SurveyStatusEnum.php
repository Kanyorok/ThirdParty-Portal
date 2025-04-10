<?php

namespace App\Enums\Feedback;

use App\Traits\UsefulEnumTrait;

enum SurveyStatusEnum: string
{
    use UsefulEnumTrait;

    case Approval = 'ap';

    case Draft = 'dr';

    case Queued = 'qu';

    case Complete = 'cc';

    case Active = 'ac';

    public function description(): string
    {
        return match ($this) {
            self::Approval => 'Pending Approval',
            self::Draft => 'Draft',
            self::Queued => 'Queued',
            self::Complete => 'Complete',
            self::Active => 'Active',
        };
    }
}
