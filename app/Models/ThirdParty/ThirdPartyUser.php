<?php

namespace App\Models\ThirdParty;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\Employee\GenderEnum;
use App\Enums\ThirdPartyTypeEnum;
use App\Enums\ThirdPartyApprovalStatusEnum;

class ThirdPartyUser extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    public static $snakeAttributes = false;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_ThirdPartyUsers';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'UserID',
        'FirstName',
        'LastName',
        'Email',
        'Phone',
        'ImageId',
        'Gender',
        'ThirdPartyId',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
        'Password',
        'EmailVerifiedOn',
    ];

    protected $hidden = [
        'Password',
        'remember_token',
    ];

    protected $casts = [
        'EmailVerifiedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
        'Gender' => GenderEnum::class,
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
        'IsActive' => 'boolean',
        'ThirdPartyId' => 'integer',
        'ImageId' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'UserID';
    }

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->FirstName} {$this->LastName}");
    }

    public function getGenderNameAttribute(): ?string
    {
        return $this->Gender?->name;
    }

    public function getEmailAttribute(): ?string
    {
        return $this->attributes['Email'] ?? null;
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['Email'] = $value;
    }

    public function scopeSuppliersOnly(Builder $query): Builder
    {
        return $query->whereHas('thirdParty', function ($q) {
            $q->where('ThirdPartyType', ThirdPartyTypeEnum::Supplier);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('IsActive', true);
    }

    public function scopeWithThirdParty(Builder $query): Builder
    {
        return $query->with('thirdParty');
    }

    public function isApproved(): bool
    {
        return $this->thirdParty?->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved;
    }

    public function isSupplier(): bool
    {
        return $this->thirdParty?->ThirdPartyType === ThirdPartyTypeEnum::Supplier;
    }

    public function isActive(): bool
    {
        return $this->IsActive === true;
    }

    public function canBeDeleted(): bool
    {
        return !$this->isActive();
    }
}
