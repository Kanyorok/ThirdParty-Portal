<?php

namespace App\Models\Licensing;

use Illuminate\Database\Eloquent\Model;

class LicenseAudit extends Model
{
    protected $table = 't_LicenseAudit';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'EventAt';
    const UPDATED_AT = null; // No updated_at timestamp

    protected $fillable = [
        'Event',
        'Detail',
        'LicenseId',
        'UserAgent',
        'IpAddress'
    ];

    protected $casts = [
        'EventAt' => 'datetime'
    ];

    /**
     * Event types
     */
    const EVENT_VALIDATED = 'validated';
    const EVENT_FAILED_SIGNATURE = 'failed_signature';
    const EVENT_EXPIRED = 'expired';
    const EVENT_WRONG_INSTANCE = 'wrong_instance';
    const EVENT_REPLAY_ATTEMPT = 'replay_attempt';
    const EVENT_MODULE_DENIED = 'module_denied';
    const EVENT_LICENSE_UPLOADED = 'license_uploaded';
    const EVENT_LICENSE_REVOKED = 'license_revoked';

    /**
     * Log a license event
     */
    public static function logEvent(string $event, string $detail = null, string $licenseId = null): void
    {
        static::create([
            'Event' => $event,
            'Detail' => $detail,
            'LicenseId' => $licenseId,
            'UserAgent' => request()->userAgent(),
            'IpAddress' => request()->ip()
        ]);
    }

    /**
     * Log successful validation
     */
    public static function logValidated(string $licenseId, array $modules = []): void
    {
        static::logEvent(
            self::EVENT_VALIDATED, 
            'Modules: ' . implode(', ', $modules), 
            $licenseId
        );
    }

    /**
     * Log validation failure
     */
    public static function logFailure(string $event, string $reason, string $licenseId = null): void
    {
        static::logEvent($event, $reason, $licenseId);
    }

    /**
     * Log module access denial
     */
    public static function logModuleDenied(string $module, string $licenseId = null): void
    {
        static::logEvent(
            self::EVENT_MODULE_DENIED, 
            "Access denied to module: {$module}", 
            $licenseId
        );
    }
}
