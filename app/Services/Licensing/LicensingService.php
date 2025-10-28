<?php

namespace App\Services\Licensing;

use App\Models\Licensing\License;
use App\Models\Licensing\Instance;
use App\Models\Licensing\LicenseAudit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LicensingService
{
    private const CACHE_KEY = 'license_validation_result';
    private const CACHE_DURATION = 300; // 5 minutes
    private const NONCE_CACHE_KEY = 'license_max_nonce';
    
    private ?string $vendorPublicKey = null;

    public function __construct()
    {
        // Load vendor public key from config/environment
        $this->vendorPublicKey = config('licensing.vendor_public_key');
    }

    /**
     * Verify and load current license
     */
    public function verifyAndLoad(): LicenseResult
    {
        try {
            // Check cache first
            $cached = Cache::get(self::CACHE_KEY);
            if ($cached) {
                return $cached;
            }

            $result = $this->performVerification();
            
            // Cache the result
            Cache::put(self::CACHE_KEY, $result, self::CACHE_DURATION);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('License verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            LicenseAudit::logFailure(
                LicenseAudit::EVENT_FAILED_SIGNATURE,
                'Verification exception: ' . $e->getMessage()
            );
            
            return LicenseResult::invalid('verification_error');
        }
    }

    /**
     * Perform actual license verification
     */
    private function performVerification(): LicenseResult
    {
        // Get latest active license
        $license = License::latest()->first();
        if (!$license) {
            return LicenseResult::invalid('no_license');
        }

        $payload = $license->PayloadJson;
        $signature = base64_decode($license->SignatureBase64);

        // 1) Verify signature
        if (!$this->verifySignature($payload, $signature)) {
            LicenseAudit::logFailure(
                LicenseAudit::EVENT_FAILED_SIGNATURE,
                'Invalid signature',
                $license->LicenseId
            );
            return LicenseResult::invalid('bad_signature');
        }

        $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        // 2) Check expiry (with small leeway for clock skew)
        if ($this->isExpired($data)) {
            LicenseAudit::logFailure(
                LicenseAudit::EVENT_EXPIRED,
                'License expired',
                $license->LicenseId
            );
            return LicenseResult::invalid('expired');
        }

        // 3) Verify instance binding
        $instance = Instance::current();
        if (!$this->verifyInstanceBinding($data, $instance)) {
            LicenseAudit::logFailure(
                LicenseAudit::EVENT_WRONG_INSTANCE,
                'Instance binding failed',
                $license->LicenseId
            );
            return LicenseResult::invalid('wrong_instance');
        }

        // 4) Check nonce for replay protection
        if (!$this->verifyNonce($data)) {
            LicenseAudit::logFailure(
                LicenseAudit::EVENT_REPLAY_ATTEMPT,
                'Replay or downgrade attempt',
                $license->LicenseId
            );
            return LicenseResult::invalid('replay_or_downgrade');
        }

        // 5) Extract and validate modules
        $allowedModules = $this->extractModules($data);

        // Update license validation timestamp
        $license->markAsValidated();

        // Log successful validation
        LicenseAudit::logValidated($license->LicenseId, $allowedModules);

        Log::info('License validated successfully', [
            'license_id' => $license->LicenseId,
            'modules' => $allowedModules,
            'tenant' => $data['tenant_name'] ?? 'Unknown'
        ]);

        return LicenseResult::ok($data, $allowedModules);
    }

    /**
     * Verify Ed25519 signature
     */
    private function verifySignature(string $payload, string $signature): bool
    {
        if (!$this->vendorPublicKey) {
            Log::error('No vendor public key configured');
            return false;
        }

        if (!function_exists('sodium_crypto_sign_verify_detached')) {
            Log::error('Sodium extension not available for signature verification');
            return false;
        }

        try {
            $publicKeyBinary = base64_decode($this->vendorPublicKey);
            return sodium_crypto_sign_verify_detached($signature, $payload, $publicKeyBinary);
        } catch (\Exception $e) {
            Log::error('Signature verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Check if license has expired
     */
    private function isExpired(array $data): bool
    {
        if (!isset($data['expires_at'])) {
            return true;
        }

        try {
            $expiryTime = Carbon::parse($data['expires_at'], 'UTC');
            $now = Carbon::now('UTC');
            
            // Allow 30 seconds leeway for clock skew
            return $now->greaterThan($expiryTime->addSeconds(30));
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Verify instance binding
     */
    private function verifyInstanceBinding(array $data, Instance $instance): bool
    {
        $licenseDbGuid = $data['instance']['db_guid'] ?? '';
        $licenseFingerprint = $data['instance']['host_fingerprint'] ?? '';

        if (!$licenseDbGuid || !$licenseFingerprint) {
            return false;
        }

        // Check DB GUID match
        if ($licenseDbGuid !== (string) $instance->DbGuid) {
            return false;
        }

        // Check host fingerprint (with some tolerance for dynamic components)
        return $instance->verifyFingerprint($licenseFingerprint);
    }

    /**
     * Verify nonce for replay protection
     */
    private function verifyNonce(array $data): bool
    {
        $nonce = $data['nonce'] ?? 0;
        $maxNonce = Cache::rememberForever(self::NONCE_CACHE_KEY, fn() => 0);

        if ($nonce < $maxNonce) {
            return false; // Replay or downgrade attempt
        }

        if ($nonce > $maxNonce) {
            Cache::forever(self::NONCE_CACHE_KEY, $nonce);
        }

        return true;
    }

    /**
     * Extract and validate modules
     */
    private function extractModules(array $data): array
    {
        $modules = $data['modules'] ?? [];
        
        // Ensure we have valid module keys
        return collect($modules)
            ->filter(fn($module) => is_string($module) && !empty($module))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get current license result (cached)
     */
    public function current(): LicenseResult
    {
        return $this->verifyAndLoad();
    }

    /**
     * Check if a specific module is licensed
     */
    public function hasModule(string $moduleKey): bool
    {
        $result = $this->current();
        return $result->allows($moduleKey);
    }

    /**
     * Check if a module ID is licensed (resolves parent module)
     */
    public function hasModuleId(int $moduleId): bool
    {
        $result = $this->current();
        if (!$result->isValid()) {
            return false;
        }

        // Use the Module model to resolve the license key
        $licenseKey = \App\Models\Core\Module::getLicenseKeyForModuleId($moduleId);
        
        if (!$licenseKey) {
            return false;
        }

        return $result->allows($licenseKey);
    }

    /**
     * Check if multiple module IDs are licensed
     */
    public function hasModuleIds(array $moduleIds): array
    {
        $result = $this->current();
        if (!$result->isValid()) {
            return array_fill_keys($moduleIds, false);
        }

        $permissions = [];
        foreach ($moduleIds as $moduleId) {
            $permissions[$moduleId] = $this->hasModuleId($moduleId);
        }

        return $permissions;
    }

    /**
     * Invalidate license cache
     */
    public function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Import a new license
     */
    public function importLicense(string $licenseId, string $payloadJson, string $signatureBase64, string $publicKeyId): bool
    {
        try {
            // Validate the license first
            $signature = base64_decode($signatureBase64);
            if (!$this->verifySignature($payloadJson, $signature)) {
                return false;
            }

            // Parse payload to validate structure
            $payload = json_decode($payloadJson, true, 512, JSON_THROW_ON_ERROR);
            if (!isset($payload['license_id'], $payload['modules'], $payload['expires_at'])) {
                return false;
            }

            // Revoke any existing active licenses
            License::where('Status', 1)->update(['Status' => 0]);

            // Create new license
            License::create([
                'LicenseId' => $licenseId,
                'PayloadJson' => $payloadJson,
                'SignatureBase64' => $signatureBase64,
                'PublicKeyId' => $publicKeyId,
                'Status' => 1
            ]);

            // Invalidate cache to force re-validation
            $this->invalidateCache();

            LicenseAudit::logEvent(
                LicenseAudit::EVENT_LICENSE_UPLOADED,
                'New license imported',
                $licenseId
            );

            return true;

        } catch (\Exception $e) {
            Log::error('License import failed', [
                'license_id' => $licenseId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get license status for admin display
     */
    public function getStatus(): array
    {
        $result = $this->current();
        $license = License::latest()->first();

        return [
            'valid' => $result->isValid(),
            'error' => $result->getError(),
            'tenant_name' => $result->getTenantName(),
            'edition' => $result->getEdition(),
            'modules' => $result->getAllowedModules(),
            'expires_at' => $result->getExpiresAt(),
            'days_until_expiry' => $result->getDaysUntilExpiry(),
            'in_grace_period' => $result->isInGracePeriod(),
            'max_users' => $result->getMaxUsers(),
            'features' => $result->getFeatures(),
            'limits' => $result->getLimits(),
            'last_validated' => $license?->LastValidatedOn,
            'created_on' => $license?->CreatedOn
        ];
    }
}
