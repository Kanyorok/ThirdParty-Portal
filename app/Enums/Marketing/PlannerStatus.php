<?php

namespace App\Enums\Marketing;

use App\Traits\UsefulEnumTrait;

enum PlannerStatus: string
{
    use UsefulEnumTrait;

    case Draft = 'dr';
    case BranchManager = 'ma';
    case MarketingManager = 'mr';
    case Committee = 'co';
    case Board = 'bo';
    case Active = 'ac';
    case Ceo = 'ce';
    case Archive = 'ar';
    case Merged = 'me';

    public function description(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::BranchManager => __('Branch Manager Approval'),
            self::Merged => __('Merged to Master Plan'),
            self::MarketingManager => __('Marketing Manager Approval'),
            self::Committee => __('Committee Approval'),
            self::Ceo => __('CEO Approval'),
            self::Board => __('Board Approval'),
            self::Active => __('Active'),
            self::Archive => __('Archive'),
        };
    }
}
