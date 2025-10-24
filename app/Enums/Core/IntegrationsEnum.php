<?php

namespace App\Enums\Core;

use App\Traits\UsefulEnumTrait;

enum IntegrationsEnum: string
{
    use UsefulEnumTrait;

    //3 char
    case ReportService = 'srs';
    case Email = 'ema';
    case DMSCoreBanking = 'cdm';
    case SMS = 'sms';
    case InfoBip = 'ibp';
    case PBX = 'pbx';
    case CoreBanking = 'cbs';
    case Channels = 'imb';
    case Facebook = 'sfb';
    case Twitter = 'xtw';
    case Website = 'web';
    case LLM = 'llm';

    public function description(): string
    {
        return match ($this) {
            self::CoreBanking => __('Core Banking '),
            self::SMS => __('Craft SMS Gateway'),
            self::InfoBip => 'Infobip (Email)',
            self::PBX => '3CX Credentials',
            self::Email => 'Email Configuration',
            self::Facebook => 'Facebook Configuration',
            self::Twitter => 'Twitter (X)',
            self::Website => 'Website Credentials',
            self::Channels => 'Internet & Mobile Banking',
            self::LLM => 'LLM (ai) Configuration',
            self::ReportService => "SQL Server Reporting Service",
            self::DMSCoreBanking => "DMS Core Banking",
        };
    }

    public function isSocial(): bool
    {
        return in_array($this, self::socials());
    }

    public static function socials(): array
    {
        return [
                self::Twitter,
                self::Facebook,
               ];
    }

    public function getIcon(string $class = ''): string
    {
        return match ($this) {
            self::Facebook => '<i class="fa-brands fa-facebook text-primary ' . $class . '"></i>',
            self::Twitter => '<i class="fa-brands fa-twitter text-primary ' . $class . '"></i>',
            default => '',
        };
    }
}
