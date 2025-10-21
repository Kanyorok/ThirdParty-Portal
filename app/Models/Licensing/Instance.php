<?php

namespace App\Models\Licensing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Instance extends Model
{
    protected $table = 't_Instance';
    protected $primaryKey = 'Id';
    
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'UpdatedOn';

    protected $fillable = [
        'DbGuid',
        'HostFingerprint',
        'AppVersion'
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'UpdatedOn' => 'datetime',
        'DbGuid' => 'string'
    ];

    /**
     * Generate instance fingerprint based on system characteristics
     */
    public static function generateFingerprint(): string
    {
        $hostname = gethostname() ?: 'unknown';
        $os = PHP_OS_FAMILY;
        $phpVersion = PHP_VERSION;
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'unknown';
        
        // Try to get a stable system identifier
        $systemId = '';
        if (function_exists('shell_exec')) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows: use computer name and system UUID if available
                $systemId = shell_exec('echo %COMPUTERNAME%') ?: '';
            } else {
                // Unix/Linux: use machine-id or hostname
                $systemId = shell_exec('cat /etc/machine-id 2>/dev/null || hostname') ?: '';
            }
            $systemId = trim($systemId);
        }

        $components = [
            $hostname,
            $os,
            $phpVersion,
            $serverSoftware,
            $systemId
        ];

        return substr(hash('sha256', implode('|', array_filter($components))), 0, 64);
    }

    /**
     * Get or create the instance record
     */
    public static function current(): self
    {
        $instance = static::first();
        
        if (!$instance) {
            $instance = static::create([
                'DbGuid' => Str::uuid(),
                'HostFingerprint' => static::generateFingerprint(),
                'AppVersion' => config('app.version', '1.0.0')
            ]);
        }
        
        return $instance;
    }

    /**
     * Verify if the provided fingerprint matches current system
     */
    public function verifyFingerprint(string $providedFingerprint): bool
    {
        $currentFingerprint = static::generateFingerprint();
        
        // Allow for some variance in dynamic components
        return $this->HostFingerprint === $providedFingerprint || 
               $currentFingerprint === $providedFingerprint;
    }
}
