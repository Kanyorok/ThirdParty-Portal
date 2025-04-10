<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;

enum RoleEnum: string
{
    use UsefulEnumTrait;

    case Read = 'r';

    case Write = 'w';

    case Share = 's';

    case Admin = 'a';
}
