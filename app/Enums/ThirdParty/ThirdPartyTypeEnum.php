<?php

namespace App\Enums\ThirdParty;

enum ThirdPartyTypeEnum: string
{
    case Supplier = 'S';
    case Tenant = 'T';
    case Customer = 'C';

    public function label(): string
    {
        return match ($this) {
            self::Supplier => 'Supplier',
            self::Tenant => 'Tenant',
            self::Customer => 'Customer',
        };
    }

    public function getCode(): string
    {
        return match ($this) {
            self::Supplier => 'SU-',
            self::Tenant => 'TE-',
            self::Customer => 'CU-',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Supplier => 'Supplier profile',
            self::Tenant => 'Tenant profile',
            self::Customer => 'Customer profile',
        };
    }

    public function requiresApproval(): bool
    {
        return match ($this) {
            self::Supplier => true,
            self::Tenant => false,
            self::Customer => false,
        };
    }

    public function requiresPrequalification(): bool
    {
        return match ($this) {
            self::Supplier => true,
            default => false,
        };
    }

    public function getModelClass(): string
    {
        return match ($this) {
            self::Supplier => \App\Models\ThirdParty\SupplierMaster::class,
            self::Tenant => \App\Models\PropertyManagement\PropertyNewTenant::class,
            self::Customer => null,
        };
    }

    public function getPrimaryKeyName(): string
    {
        return match ($this) {
            self::Supplier => 'SupplierID',
            self::Tenant => 'TenantID',
            self::Customer => 'CustomerID',
        };
    }

    public static function fromCode(string $code): ?self
    {
        return match (true) {
            str_starts_with($code, 'SU-') => self::Supplier,
            str_starts_with($code, 'TE-') => self::Tenant,
            str_starts_with($code, 'CU-') => self::Customer,
            default => null,
        };
    }
}
