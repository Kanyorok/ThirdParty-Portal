<?php

namespace App\Enums\Marketing;

use App\Traits\UsefulEnumTrait;

enum PlannerTypeEnum: string
{
    use UsefulEnumTrait;

    case MasterPlanner = 'mp';
    case BranchPlanner = 'bp';
}
