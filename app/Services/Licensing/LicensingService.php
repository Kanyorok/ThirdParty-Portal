<?php

namespace App\Services\Licensing;

use App\Models\Licensing\Instance;
use App\Models\Licensing\LicenseAudit;
use App\Models\Licensing\LicenseRecord;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LicensingService
{
    private const CACHE_KEY = 'licensing.current';

    public function current(): LicenseResult
    {
        // Bypass licensing in non-production environments
        if ($this->shouldBypassLicensing()) {
            return $this->getDevelopmentLicense();
        }

        /** @var CacheRepository $cache */
        $cache = Cache::store();
        $ttlSeconds = (int)config('licensing.cache_ttl_seconds', 300);
        $cached = $cache->get(self::CACHE_KEY);
        if ($cached instanceof LicenseResult) {
            return $cached;
        }

        $result = $this->verifyAndLoad();
        $cache->put(self::CACHE_KEY, $result, $ttlSeconds);

        return $result;
    }

    public function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function verifyAndLoad(): LicenseResult
    {
        $record = LicenseRecord::query()
            ->where('Status', 1)
            ->orderByDesc('Id')
            ->first();

        if (! $record) {
            $this->audit('missing', 'No active license record');

            return LicenseResult::invalid('missing');
        }

        $payload = $record->PayloadJson;
        $signature = base64_decode($record->SignatureBase64, true);
        if ($signature === false) {
            $this->audit('bad_signature', 'Signature not base64');

            return LicenseResult::invalid('bad_signature');
        }

        // Ensure the stored public key id matches configured one
        $configuredKeyId = (string)config('licensing.public_key_id', '');
        if ($configuredKeyId !== '' && strcasecmp($configuredKeyId, (string)$record->PublicKeyId) !== 0) {
            $this->audit('kid_mismatch', 'PublicKeyId mismatch');

            return LicenseResult::invalid('kid_mismatch');
        }

        $publicKeyBase64 = (string)config('licensing.public_key_base64', '');
        if ($publicKeyBase64 === '') {
            Log::warning('Licensing public key missing in configuration');

            return LicenseResult::invalid('server_misconfigured');
        }

        $publicKey = base64_decode($publicKeyBase64, true);
        if ($publicKey === false) {
            $this->audit('bad_key', 'Public key not base64');

            return LicenseResult::invalid('server_misconfigured');
        }

        if (! function_exists('sodium_crypto_sign_verify_detached')) {
            $this->audit('crypto_missing', 'libsodium not available');

            return LicenseResult::invalid('server_misconfigured');
        }

        $ok = sodium_crypto_sign_verify_detached($signature, $payload, $publicKey);
        if (! $ok) {
            $this->audit('bad_signature', 'Signature verification failed');

            return LicenseResult::invalid('bad_signature');
        }

        $data = json_decode($payload, true);
        if (! is_array($data)) {
            $this->audit('bad_payload', 'JSON decode failed');

            return LicenseResult::invalid('bad_payload');
        }

        $now = CarbonImmutable::now('UTC');
        // Accept expires_at as ISO string or epoch seconds
        $expiresAt = null;
        if (isset($data['expires_at'])) {
            $exp = $data['expires_at'];
            if (is_numeric($exp)) {
                $expiresAt = CarbonImmutable::createFromTimestampUTC((int)$exp);
            } else {
                try {
                    $expiresAt = CarbonImmutable::parse((string)$exp);
                } catch (\Throwable $e) {
                    $expiresAt = null;
                }
            }
        }
        if (! $expiresAt) {
            $this->audit('bad_payload', 'Missing expires_at');

            return LicenseResult::invalid('bad_payload');
        }

        $graceSeconds = (int)config('licensing.grace_period_seconds', 0);
        if ($now->greaterThan($expiresAt->addSeconds($graceSeconds))) {
            $this->audit('expired', 'License expired');

            return LicenseResult::invalid('expired');
        }

        $instance = Instance::query()->orderBy('Id')->first();
        if (! $instance) {
            $this->audit('instance_missing', 't_Instance row missing');

            return LicenseResult::invalid('server_misconfigured');
        }

        $payloadDbGuid = (string)($data['instance']['db_guid'] ?? '');
        if ($payloadDbGuid === '' || strcasecmp($payloadDbGuid, (string)$instance->DbGuid) !== 0) {
            $this->audit('wrong_instance', 'DbGuid mismatch');

            return LicenseResult::invalid('wrong_instance');
        }

        $nonce = (int)($data['nonce'] ?? 0);
        if ($nonce < (int)$instance->MaxSeenNonce) {
            $this->audit('replay', 'Nonce lower than MaxSeenNonce');

            return LicenseResult::invalid('replay_or_downgrade');
        }

        if ($nonce > (int)$instance->MaxSeenNonce) {
            $instance->MaxSeenNonce = $nonce;
            $instance->save();
        }

        $allowedModules = collect($data['modules'] ?? [])
            ->map(static fn ($v) => (int)$v)
            ->unique()->values()->all();

        $record->LastValidatedOn = $now->toDateTimeString();
        $record->save();

        $this->audit('validated', 'License valid');

        return LicenseResult::ok($data, $allowedModules, $expiresAt->toIso8601String());
    }

    /**
     * Check if licensing should be bypassed based on environment
     */
    private function shouldBypassLicensing(): bool
    {
        // Check if LICENSING_BYPASS is explicitly set in environment
        $bypass = config('licensing.bypass', false);
        if ($bypass === true || $bypass === 'true' || $bypass === '1') {
            return true;
        }

        // Auto-bypass for local and development environments
        $env = app()->environment();

        return in_array($env, ['local', 'development', 'dev', 'testing'], true);
    }

    /**
     * Return a development license that's always valid
     */
    private function getDevelopmentLicense(): LicenseResult
    {
        // Development module IDs that should be enabled
        $allowedModules = [
            100000,   // Module 1
            200000,   // Module 2
            300000,   // Module 3
            400000,   // Module 4
            500000,   // Module 5
            600000,   // Module 6
            700000,   // Module 7
            800000,   // Module 8
            900000,   // Module 9
            1000000,  // Module 10
            1100000,  // Module 11
            1200000,  // Module 12
            9800000,  // Module 98
            9900000,  // Module 99
        ];

        // Create a development license with all required modules enabled
        $developmentPayload = [
            'tenant_name' => 'Development Environment',
            'edition' => 'Development',
            'max_users' => 999999,
            'expires_at' => now()->addYears(100)->toIso8601String(),
            'features' => [],
            'limits' => [],
            'modules' => $allowedModules,
            'instance' => [
                'db_guid' => 'dev-bypass',
            ],
            'nonce' => 0,
        ];

        return LicenseResult::ok(
            $developmentPayload,
            $allowedModules,
            now()->addYears(100)->toIso8601String()
        );
    }

    private function audit(string $event, ?string $detail = null): void
    {
        try {
            LicenseAudit::create([
                'Event' => $event,
                'Detail' => $detail,
                'EventAt' => now('UTC')->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
        }
    }
}

final class LicenseResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly ?array $payload,
        public readonly array $allowedModules,
        public readonly ?string $reason,
        public readonly ?string $expiresAt
    ) {
    }

    public static function invalid(string $reason): self
    {
        return new self(false, null, [], $reason, null);
    }

    public static function ok(array $payload, array $allowedModules, string $expiresAt): self
    {
        return new self(true, $payload, $allowedModules, null, $expiresAt);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function allows(int $moduleId): bool
    {
        return in_array($moduleId, $this->allowedModules, true);
    }
}
