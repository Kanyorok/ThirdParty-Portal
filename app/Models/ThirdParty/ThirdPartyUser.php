<?php

namespace App\Models\ThirdParty;

use App\Enums\Employee\GenderEnum;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Notifications\ThirdParty\VerifyThirdPartyEmail;
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
        'FirstName',
        'LastName',
        'Email',
        'Phone',
        'ImageId',
        'Gender',
        'ThirdPartyId',
        'Password',
        'EmailVerifiedOn',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
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
        'IsActive' => 'boolean',
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
        'Password' => 'hashed',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->UserID)) {
                do {
                    $model->UserID = strtoupper(Str::random(8));
                } while (static::where('UserID', $model->UserID)->exists());
            }
            // Self-registration: temporarily set to 0, will update after creation
            // $model->CreatedBy ??= 0;
            // $model->ModifiedBy ??= 0;
        });

        // static::created(function ($model) {
        //     // Update CreatedBy to self after creation
        //     if ($model->CreatedBy === 0) {
        //         $model->timestamps = false;
        //         $model->update([
        //             'CreatedBy' => $model->Id,
        //             'ModifiedBy' => $model->Id,
        //         ]);
        //         $model->timestamps = true;
        //     }
        // });
    }

    public function getAuthIdentifierName(): string
    {
        return 'Id';
    }

    public function getAuthPassword(): string
    {
        return $this->Password;
    }

    public function getRouteKeyName(): string
    {
        return 'UserID';
    }

    public function getEmailForVerification(): string
    {
        return $this->Email;
    }

    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->EmailVerifiedOn);
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill(['EmailVerifiedOn' => $this->freshTimestamp()])->save();
    }

    // public function sendEmailVerificationNotification(): void
    // {
    //     $this->notify(new VerifyThirdPartyEmail);
    // }
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'CountryId', 'Id');
    }

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->FirstName} {$this->LastName}");
    }

    public function isActive(): bool
    {
        return $this->IsActive === true;
    }

    public function isApproved(): bool
    {
        if (!$this->IsActive) {
            return false;
        }

        if (is_null($this->ThirdPartyId)) {
            return true;
        }

        return $this->thirdParty?->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved;
    }

    public function isSupplier(): bool
    {
        return $this->thirdParty?->isSupplier() ?? false;
    }

    public function isTenant(): bool
    {
        return $this->thirdParty?->isTenant() ?? false;
    }

    public function isCustomer(): bool
    {
        return $this->thirdParty?->isCustomer() ?? false;
    }

    public function hasProfile(): bool
    {
        return !is_null($this->ThirdPartyId);
    }

    public function canBeDeleted(): bool
    {
        return !$this->isActive();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('IsActive', true);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('EmailVerifiedOn');
    }

    public function scopeWithThirdParty(Builder $query): Builder
    {
        return $query->with('thirdParty');
    }

    public function scopeByEmail(Builder $query, string $email): Builder
    {
        return $query->where('Email', strtolower($email));
    }

    public function scopeSuppliersOnly(Builder $query): Builder
    {
        return $query->whereHas('thirdParty', fn($q) => $q->suppliers());
    }

    public function scopeTenantsOnly(Builder $query): Builder
    {
        return $query->whereHas('thirdParty', fn($q) => $q->tenants());
    }

    public function scopeCustomersOnly(Builder $query): Builder
    {
        return $query->whereHas('thirdParty', fn($q) => $q->customers());
    }

    public static function findByEmail(string $email): ?self
    {
        return static::byEmail($email)->first();
    }

    public static function getPrimaryKey(): string
    {
        return 'ThirdPartyUserId';
    }
}
