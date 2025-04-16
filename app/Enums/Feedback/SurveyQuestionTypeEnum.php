<?php

namespace App\Enums\Feedback;

use App\Traits\UsefulEnumTrait;

enum SurveyQuestionTypeEnum: string
{
    use UsefulEnumTrait;

    case Open = 'op';

    case Closed = 'cl';

    public function description(): string
    {
        return match ($this) {
            self::Open => 'Text Input',
            self::Closed => 'Options Provided',
        };
    }
}
