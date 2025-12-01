<?php

namespace App\Models\ThirdParty;

use App\Enums\Employee\GenderEnum;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Traits\Model\UserActorTrait;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class ThirdPartyUser extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens, Notifiable, SoftDeletes, MustVerifyEmail, UserActorTrait;

    public static $snakeAttributes = false;

    const string CREATED_AT = 'CreatedOn';
    const string UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';

    protected $table = 't_ThirdPartyUsers';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'FirstName', 'LastName', 'Email', 'Phone', 'ImageId', 'Gender', 'ThirdPartyId', 'Password', 'EmailVerifiedOn', 'IsActive',
        'CreatedBy', 'ModifiedBy', 'DeletedBy',
    ];

    protected $hidden = [
        'Password',
        'remember_token',
        'UserID',
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

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->UserID)) {
                do {
                    $model->UserID = strtoupper(Str::random(6));
                } while (static::where('UserID', $model->UserID)->exists());
                $model->IsActive = false;
            }
        });
    }

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
            // Prefer pivot relationship filtering
            $q->whereHas('types', function ($t) {
                $t->where('Code', 'like', 'SU-%');
            })->orWhere('ThirdPartyType', ThirdPartyTypeEnum::Supplier); // legacy fallback
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
        return $this->IsActive && $this->thirdParty && $this->thirdParty->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved;
    }

    public function isSupplier(): bool
    {
        if ($this->thirdParty && $this->thirdParty->relationLoaded('types')) {
            return $this->thirdParty->types->pluck('Code')->contains(fn($c) => str_starts_with($c, 'SU-'))
                || $this->thirdParty->types->pluck('TypeId')->contains(fn($id) => $id === $this->thirdParty->ThirdPartyType); // safety
        }
        // Fallback to legacy enum column
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

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmail);
    }

    public static function getPrimaryKey(): string
    {
        return 'ThirdPartyUserId';
    }
}
