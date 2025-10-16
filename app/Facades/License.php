<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * License Facade
 * 
 * Provides easy access to licensing functionality throughout the application.
 * 
 * @method static bool hasModule(string $moduleKey) Check if a module is licensed
 * @method static bool hasModuleId(int $moduleId) Check if a module ID is licensed (resolves parent)
 * @method static array hasModuleIds(array $moduleIds) Check multiple module IDs
 * @method static \App\Services\Licensing\LicenseResult current() Get current license result
 * @method static array getStatus() Get license status information
 * @method static bool isValid() Check if current license is valid
 * @method static void invalidateCache() Clear license cache
 * 
 * @example
 *   License::hasModule('PROCUREMENT') // Check if procurement is licensed
 *   License::hasModuleId(301000) // Check if procurement sub-module is licensed
 *   License::current()->getTenantName() // Get tenant name
 *   License::getStatus()['edition'] // Get license edition
 */
class License extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\Licensing\LicensingService::class;
    }
}
