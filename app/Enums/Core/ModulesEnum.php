<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;

enum ModulesEnum: int
{
    use UsefulEnumTrait;

    case ThirdParty = 100000;
    case CRM = 200000;
    case Procurement = 300000;
    case Inventory = 400000;
    case Property = 500000;
    case Fleet = 600000;
    case DMS = 700000;
    case Legal = 800000;
    case Insurance = 900000;
    case HRM = 1000000;
    case Finance = 1100000;
    case BudgetLine = 1200000;
    case Settings = 9800000;
    case MyAccount = 9900000;

    public function description(): string
    {
        return match ($this) {
            self::ThirdParty => 'Third Parties',
            self::CRM => 'Customer Management',
            self::Procurement => 'Procurement',
            self::Inventory => 'Inventory',
            self::Property => 'Property Management',
            self::Fleet => 'Fleet Management',
            self::DMS => 'Document Management',
            self::Legal => 'Legal',
            self::Insurance => 'Bank Assurance',
            self::HRM => 'User Management',
            self::Finance => 'Finance',
            self::Settings => 'Settings',
            self::BudgetLine => 'Budget & Analytics',
            self::MyAccount => 'My Account',
        };
    }
}
