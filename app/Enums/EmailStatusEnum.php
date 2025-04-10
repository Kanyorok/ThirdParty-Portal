<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum EmailStatusEnum: string
{
    use UsefulEnumTrait;

    case Read = 'r';

    case Unread = 'u';

    case Queued = 'q';

    case Draft = 'd';

    case Sent = 's';

    case Failed = 'f';

    case Sending = 'p';

    public function badge(): string
    {
        return match ($this->value) {
            self::Draft->value => '<span class="mx-1 badge badge-info-light">DRAFT</span>',
            self::Queued->value, self::Sending->value => '<span class="mx-1 badge badge-primary-light">SENDING</span>',
            self::Failed->value => '<span class="mx-1 badge badge-danger-light">FAILED</span>',
            self::Sent->value => '<span class="mx-1 badge badge-success-light">SENT</span>',
            self::Unread->value => '<span class="mx-1 badge badge-secondary-light">UNREAD</span>',
            default => '',
        };
    }
}
