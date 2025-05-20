<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;

enum ModulesEnum: int
{
    use UsefulEnumTrait;

    case Inventory = 100000;
    case Procurement = 200000;
    case DMS = 300000;
    case FleetManagement = 400000;
    case PropertyManagement = 500000;
    case Insurance = 600000;
    case Legal = 700000;
    case Finance = 800000;
    case HRM = 900000;
    case CRM = 1000000;

    case Settings = 9000000;
}
