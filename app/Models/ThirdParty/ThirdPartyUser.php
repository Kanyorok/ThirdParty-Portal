<?php

namespace App\Models\ThirdParty;

use App\Enums\Employee\GenderEnum;
use App\Models\Core\Country;
use App\Notifications\ThirdParty\VerifyThirdPartyEmail;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ThirdPartyUser extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens, Notifiable, SoftDeletes, MustVerifyEmail;

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
        'Gender' => GenderEnum::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->UserID)) {
                $model->UserID = self::generateUniqueUserId();
            }
        });
    }

    private static function generateUniqueUserId(): string
    {
        do {
            $id = strtoupper(Str::random(8));
        } while (static::where('UserID', $id)->exists());
        return $id;
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

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyThirdPartyEmail);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'CountryId', 'Id');
    }

    public function thirdParty(): BelongsTo
    {
        return $this->belongsTo(ThirdParties::class, 'ThirdPartyId', 'Id');
    }

    public function fullName(): Attribute
    {
        return Attribute::get((fn() => trim("{$this->FirstName} {$this->LastName}")));
    }

    public function isActive(): bool
    {
        return $this->IsActive === true;
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

    public static function findByEmail(string $email): ?self
    {
        return static::byEmail($email)->first();
    }

    public static function getPrimaryKey(): string
    {
        return 'Id';
    }

    public function getMorphClass(): string
    {
        return 'ThirdPartyUser';
    }
}
