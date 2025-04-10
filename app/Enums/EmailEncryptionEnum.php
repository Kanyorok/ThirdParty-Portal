<?php

namespace App\Enums;

use App\Traits\UsefulEnumTrait;

enum EmailEncryptionEnum: string
{
    use UsefulEnumTrait;

    case NONE = 'none';
    case TLS = 'tls';
    case SSL = 'ssl';
}
