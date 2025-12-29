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

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class ThirdPartyUser extends Authenticatable implements MustVerifyEmailContract, CanResetPasswordContract
{
    use HasApiTokens, Notifiable, SoftDeletes, MustVerifyEmail, UserActorTrait, CanResetPassword;

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
        'CreatedBy' => 'integer',
        'ModifiedBy' => 'integer',
        'DeletedBy' => 'integer',
        'IsActive' => 'boolean',
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
            $model->IsActive = true;
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
                && $this->thirdParty->status?->Value === ThirdPartyApprovalStatusEnum::Approved->value;
        }

        //@Kimxons: Approval is based solely on their individual 'IsActive' status.
        return $this->IsActive;
    }

    /**
     * Checks if the user is associated with a Third Party that has a Supplier profile.
     * Delegates the check to the ThirdParties model.
     */
    /**
     * Checks if the user is associated with a Third Party that has a Supplier profile.
     * Delegates the check to the ThirdParties model.
     */
    public function isSupplier(): bool
    {
        return $this->thirdParty?->isSupplier() ?? false;
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

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return !is_null($this->EmailVerifiedOn);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'EmailVerifiedOn' => $this->freshTimestamp(),
            'IsActive' => true, // Activate user upon verification so they can login to complete profile
        ])->save();
    }

    /**
     * Get the name of the "email verified at" column.
     *
     * @return string
     */
    public function getEmailVerifiedAtColumn()
    {
        return 'EmailVerifiedOn';
    }

    public function getEmailForVerification(): string
    {
        return $this->Email;
    }

    public function sendEmailVerificationNotification()
    {
        // 1. Generate the Signed Backend URL (which verifies the signature)
        $backendSignedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            \Carbon\Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
                'type' => 'user' // Disambiguate User vs Party
            ]
        );

        // 2. Construct the Frontend URL
        // The link in the email should point to the Frontend (e.g. localhost:3000/verify-email)
        // We pass the backend signed URL as a parameter 'verify_url' so the frontend can call it.
        // NOTE: (auth) is a route group, so it does NOT appear in the URL.
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $url = $frontendUrl . '/verify-email?verify_url=' . urlencode($backendSignedUrl);

        $body = '<p>Please click the button below to verify your email address.</p>';
        $body .= '<p><a href="' . $url . '" style="background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Verify Email Address</a></p>';
        $body .= '<p style="font-size: small; color: #666; margin-top: 20px;">If the button above does not work, copy and paste the following link into your browser:<br>' . $url . '</p>';
        $body .= '<p>If you did not create an account, no further action is required.</p>';

        \App\Services\CRMEmailService::createRaw(
            \App\Helpers\SystemHelper::user(),
            'Verify Email Address - ' . config('app.name'),
            $body,
            [['Name' => $this->getFullNameAttribute(), 'Email' => $this->Email]]
        )->send(true);
    }

    public function getEmailForPasswordReset()
    {
        return $this->Email;
    }

    public function sendPasswordResetNotification($token): void
    {
        // Construct the reset URL for the frontend
        // Assuming frontend runs on localhost:3000 or using config
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $url = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($this->Email);

        $body = '<p>You are receiving this email because we received a password reset request for your account.</p>';
        $body .= '<p><a href="' . $url . '">Reset Password</a></p>';
        $body .= '<p>This password reset link will expire in 60 minutes.</p>';
        $body .= '<p>If you did not request a password reset, no further action is required.</p>';

        \App\Services\CRMEmailService::createRaw(
            \App\Helpers\SystemHelper::user(),
            'Reset Password Notification',
            $body,
            [['Name' => $this->getFullNameAttribute(), 'Email' => $this->Email]]
        )->send(true);
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
