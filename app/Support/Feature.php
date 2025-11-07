<?php

namespace App\Support;

use App\Services\Licensing\LicensingService;
use App\Models\Licensing\LicenseAudit;

/**
 * Feature class for checking licensed modules and features
 * 
 * Provides a clean API for checking licensing in application code.
 */
class Feature
{
    private static ?LicensingService $licensingService = null;

    /**
     * Get the licensing service instance
     */
    private static function getLicensingService(): LicensingService
    {
        if (self::$licensingService === null) {
            self::$licensingService = app(LicensingService::class);
        }

        return self::$licensingService;
    }

    /**
     * Check if a module is licensed and throw exception if not
     */
    public static function requires(string $moduleKey): void
    {
        if (!self::hasModule($moduleKey)) {
            LicenseAudit::logModuleDenied($moduleKey);
            abort(403, "Module '{$moduleKey}' is not licensed");
        }
    }

    /**
     * Check if multiple modules are all licensed and throw exception if any missing
     */
    public static function requiresAll(array $moduleKeys): void
    {
        foreach ($moduleKeys as $moduleKey) {
            self::requires($moduleKey);
        }
    }

    /**
     * Check if at least one of the modules is licensed and throw exception if none
     */
    public static function requiresAny(array $moduleKeys): void
    {
        foreach ($moduleKeys as $moduleKey) {
            if (self::hasModule($moduleKey)) {
                return; // At least one is licensed
            }
        }

        LicenseAudit::logModuleDenied(implode(',', $moduleKeys));
        abort(403, 'None of the required modules are licensed: ' . implode(', ', $moduleKeys));
    }

    /**
     * Check if a module is licensed
     */
    public static function hasModule(string $moduleKey): bool
    {
        return self::getLicensingService()->hasModule($moduleKey);
    }

    /**
     * Check if a module ID is licensed (resolves parent module)
     */
    public static function hasModuleId(int $moduleId): bool
    {
        return self::getLicensingService()->hasModuleId($moduleId);
    }

    /**
     * Check if a premium feature is enabled
     */
    public static function hasFeature(string $featureKey): bool
    {
        $license = self::getLicensingService()->current();
        return $license->hasFeature($featureKey);
    }

    /**
     * Check if a premium feature is enabled and throw exception if not
     */
    public static function requiresFeature(string $featureKey): void
    {
        if (!self::hasFeature($featureKey)) {
            LicenseAudit::logEvent(
                LicenseAudit::EVENT_MODULE_DENIED,
                "Feature access denied: {$featureKey}"
            );
            abort(403, "Feature '{$featureKey}' is not licensed");
        }
    }

    /**
     * Get current license edition
     */
    public static function getEdition(): string
    {
        return self::getLicensingService()->current()->getEdition();
    }

    /**
     * Get licensed modules list
     */
    public static function getLicensedModules(): array
    {
        return self::getLicensingService()->current()->getAllowedModules();
    }

    /**
     * Check if current license is valid
     */
    public static function isLicenseValid(): bool
    {
        return self::getLicensingService()->current()->isValid();
    }

    /**
     * Get days until license expires
     */
    public static function getDaysUntilExpiry(): ?int
    {
        return self::getLicensingService()->current()->getDaysUntilExpiry();
    }

    /**
     * Check if in grace period
     */
    public static function isInGracePeriod(): bool
    {
        return self::getLicensingService()->current()->isInGracePeriod();
    }

    /**
     * Get usage limit for a specific type
     */
    public static function getLimit(string $limitType): ?int
    {
        return self::getLicensingService()->current()->getLimit($limitType);
    }

    /**
     * Check if under usage limit
     */
    public static function isUnderLimit(string $limitType, int $currentUsage): bool
    {
        $limit = self::getLimit($limitType);
        
        if ($limit === null) {
            return true; // No limit set
        }

        return $currentUsage < $limit;
    }

    /**
     * Enforce usage limit
     */
    public static function enforceLimit(string $limitType, int $currentUsage): void
    {
        if (!self::isUnderLimit($limitType, $currentUsage)) {
            $limit = self::getLimit($limitType);
            abort(403, "Usage limit exceeded for {$limitType}. Current: {$currentUsage}, Limit: {$limit}");
        }
    }

    /**
     * Get tenant name from license
     */
    public static function getTenantName(): string
    {
        return self::getLicensingService()->current()->getTenantName();
    }

    /**
     * Get max users allowed
     */
    public static function getMaxUsers(): int
    {
        return self::getLicensingService()->current()->getMaxUsers();
    }

    /**
     * Check if user can access based on current edition
     */
    public static function canAccess(string $moduleKey): bool
    {
        // Always allow Settings and Account modules
        if (in_array($moduleKey, ['SETTINGS', 'ACCOUNT'])) {
            return true;
        }

        return self::hasModule($moduleKey);
    }

    /**
     * Get licensing status summary for admin display
     */
    public static function getStatusSummary(): array
    {
        return self::getLicensingService()->getStatus();
    }
}
