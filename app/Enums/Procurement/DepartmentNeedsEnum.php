<?php

namespace App\Enums\Procurement;

use App\Traits\UsefulEnumTrait;

enum DepartmentNeedsEnum:string
{
    use UsefulEnumTrait;
    case Approval = 'a';

    case Draft = 'd';

}