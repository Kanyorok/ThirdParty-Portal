<?php

namespace App\Services\Licensing;

class LicenseResult
{
    private bool $valid;
    private string $error;
    private ?array $payload;
    private array $allowedModules;

    public function __construct(bool $valid, string $error = '', ?array $payload = null, array $allowedModules = [])
    {
        $this->valid = $valid;
        $this->error = $error;
        $this->payload = $payload;
        $this->allowedModules = $allowedModules;
    }

    public static function ok(array $payload, array $allowedModules): self
    {
        return new self(true, '', $payload, $allowedModules);
    }

    public static function invalid(string $error): self
    {
        return new self(false, $error);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function getAllowedModules(): array
    {
        return $this->allowedModules;
    }

    public function allows(string $moduleKey): bool
    {
        return $this->valid && in_array($moduleKey, $this->allowedModules, true);
    }

    public function getTenantName(): string
    {
        return $this->payload['tenant_name'] ?? 'Unknown';
    }

    public function getEdition(): string
    {
        return $this->payload['edition'] ?? 'Unknown';
    }

    public function getMaxUsers(): int
    {
        return $this->payload['max_users'] ?? 0;
    }

    public function getExpiresAt(): ?string
    {
        return $this->payload['expires_at'] ?? null;
    }

    public function getFeatures(): array
    {
        return $this->payload['features'] ?? [];
    }

    public function getLimits(): array
    {
        return $this->payload['limits'] ?? [];
    }

    public function hasFeature(string $feature): bool
    {
        return ($this->payload['features'][$feature] ?? false) === true;
    }

    public function getLimit(string $limitType): ?int
    {
        return $this->payload['limits'][$limitType] ?? null;
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->valid || !$this->payload) {
            return null;
        }

        $expiresAt = $this->getExpiresAt();
        if (!$expiresAt) {
            return null;
        }

        try {
            $expiry = new \DateTime($expiresAt);
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $diff = $now->diff($expiry);
            
            return $diff->invert ? -$diff->days : $diff->days;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if license is in grace period (expired but within grace days)
     */
    public function isInGracePeriod(int $graceDays = 7): bool
    {
        $daysUntilExpiry = $this->getDaysUntilExpiry();
        
        if ($daysUntilExpiry === null) {
            return false;
        }

        // If negative, it means expired. Check if within grace period.
        return $daysUntilExpiry < 0 && abs($daysUntilExpiry) <= $graceDays;
    }
}
