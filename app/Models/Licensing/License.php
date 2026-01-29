<?php

namespace App\Models\Licensing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $table = 't_Licenses';
    protected $primaryKey = 'Id';

    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = null; // No automatic updated_at

    protected $fillable = [
        'LicenseId',
        'PayloadJson',
        'SignatureBase64',
        'PublicKeyId',
        'Status',
        'LastValidatedOn',
    ];

    protected $casts = [
        'CreatedOn' => 'datetime',
        'LastValidatedOn' => 'datetime',
        'Status' => 'integer',
    ];

    /**
     * Scope for active licenses only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('Status', 1);
    }

    /**
     * Get the latest active license
     */
    public static function latest()
    {
        return static::active()->orderBy('CreatedOn', 'desc');
    }

    /**
     * Get parsed license payload
     */
    public function getParsedPayloadAttribute(): ?array
    {
        try {
            return json_decode($this->PayloadJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }
    }

    /**
     * Check if license has expired
     */
    public function getIsExpiredAttribute(): bool
    {
        $payload = $this->parsed_payload;
        if (! $payload || ! isset($payload['expires_at'])) {
            return true;
        }

        try {
            $expiryDate = new \DateTime($payload['expires_at']);

            return $expiryDate <= new \DateTime('now', new \DateTimeZone('UTC'));
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * Get modules from license payload
     */
    public function getModulesAttribute(): array
    {
        $payload = $this->parsed_payload;

        return $payload['modules'] ?? [];
    }

    /**
     * Get license tenant info
     */
    public function getTenantInfoAttribute(): array
    {
        $payload = $this->parsed_payload;

        return [
            'name' => $payload['tenant_name'] ?? 'Unknown',
            'id' => $payload['tenant_id'] ?? null,
            'edition' => $payload['edition'] ?? 'Unknown',
        ];
    }

    /**
     * Update last validated timestamp
     */
    public function markAsValidated(): void
    {
        $this->update(['LastValidatedOn' => now('UTC')]);
    }
}
