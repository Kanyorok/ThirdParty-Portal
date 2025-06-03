<?php

namespace App\Enums;
use App\Traits\UsefulEnumTrait;

enum ProcurementPlanStatusEnum: string
{
    use UsefulEnumTrait;

    case Draft = 'Dr';
    case Submitted = 'Su';
    case Approved = 'Ap';
    case Rejected = 'Re';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'warning',
            self::Submitted => 'info',  
            self::Approved => 'success', 
            self::Rejected => 'danger',  
        };
    }
}