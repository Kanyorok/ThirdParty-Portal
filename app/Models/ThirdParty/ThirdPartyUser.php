<?php

namespace App\Models\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Models\Core\Approval\CodeDetail;
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
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
        'Password' => 'hashed',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->UserID)) {
                do {
                    $model->UserID = strtoupper(Str::random(8));
                } while (static::where('UserID', $model->UserID)->exists());
            }
            $model->IsActive = false;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'UserID';
    }


    public function gender(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'Gender', 'Id');
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

    /**
     * @Kimxons: Cases handled
     * 1. user of a Third Party: Requires user active + company/business approved.
     * 2. Individual Customer: Requires only user active ("self-approved").
     */
    public function isApproved(): bool
    {
        if (!empty($this->ThirdPartyId)) {
            return $this->IsActive
                && $this->thirdParty
                && $this->thirdParty->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved;
        }

        //@Kimxons: Approval is based solely on their individual 'IsActive' status.
        return $this->IsActive;
    }

    /**
     * Checks if the user is associated with a Third Party that has a Supplier profile.
     * Delegates the check to the ThirdParties model.
     */
    public function isSupplier(): bool
    {
        if ($this->thirdParty && $this->thirdParty->relationLoaded('types')) {
            return $this->thirdParty->types->pluck('Code')->contains(fn($c) => str_starts_with($c, 'SU-'))
                || $this->thirdParty->types->pluck('TypeId')->contains(fn($id) => $id === $this->thirdParty->ThirdPartyType);
        }
        return $this->thirdParty?->ThirdPartyType === ThirdPartyTypeEnum::Supplier;
    }

    /**
     * Checks if the user is associated with a Third Party that has a Tenant profile.
     * Delegates the check to the ThirdParties model.
     */
    public function isTenant(): bool
    {
        return $this->thirdParty?->isTenant() ?? false;
    }

    /**
     * Checks if the user is associated with a Third Party that has a Customer profile.
     * Delegates the check to the ThirdParties model.
     */
    public function isCustomer(): bool
    {
        return $this->thirdParty?->isCustomer() ?? false;
    }

    public function isActive(): bool
    {
        return $this->IsActive === true;
    }

    public function canBeDeleted(): bool
    {
        return !$this->isActive();
    }

    public function getEmailForVerification(): string
    {
        return $this->Email;
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmail);
    }

    public function scopeSuppliersOnly(Builder $query): Builder
    {
        return $query->whereHas('thirdParty', function ($q) {
            $q->whereHas('types', function ($t) {
                $t->where('Code', 'like', 'SU-%');
            })->orWhere('ThirdPartyType', ThirdPartyTypeEnum::Supplier);
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

    public static function getPrimaryKey(): string
    {
        return 'ThirdPartyUserId';
    }
}
