<?php

namespace App\Models\ThirdParty;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Country;
use App\Notifications\ThirdParty\VerifyThirdPartyEmail;
// use Illuminate\Auth\MustVerifyEmail;
// use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\Model\UserActorTrait;
use App\Services\CRMEmailService;
use App\Models\Auth\User;
use App\Enums\EmailPriorityEnum;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class ThirdPartyUser extends Authenticatable implements CanResetPasswordContract
{
    // use HasApiTokens, Notifiable, SoftDeletes, MustVerifyEmail;
    use HasApiTokens, Notifiable, SoftDeletes, UserActorTrait, CanResetPassword;

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
        'IsActive' => 'boolean',
        'Password' => 'hashed',
        // 'Gender' => GenderEnum::class,
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

    public ?string $verificationBaseUrl = null;

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'EmailVerifiedOn' => $this->freshTimestamp(),
            'IsActive' => true, // Activate user upon verification
        ])->save();
    }

    public function sendEmailVerificationNotification(): void
    {
        // 1. Generate the Signed Backend URL (which verifies the signature)
        $backendSignedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            \Illuminate\Support\Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );

        // 2. Construct the Frontend URL
        // Use dynamically provided base URL if available, otherwise fallback to config
        $frontendUrl = $this->verificationBaseUrl ?? config('app.frontend_url', config('app.nextauth_url', 'http://localhost:3000'));
        $frontendUrl = rtrim($frontendUrl, '/');

        $url = $frontendUrl . '/verify-email?verify_url=' . urlencode($backendSignedUrl);

        $body = '<p>Please click the button below to verify your email address.</p>';
        $body .= '<p><a href="' . $url . '" style="background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Verify Email Address</a></p>';
        $body .= '<p style="font-size: small; color: #666; margin-top: 20px;">If the button above does not work, copy and paste the following link into your browser:<br>' . $url . '</p>';
        $body .= '<p>If you did not create an account, no further action is required.</p>';

        \App\Services\CRMEmailService::createRaw(
            \App\Helpers\SystemHelper::user(),
            'Verify Email Address - ' . config('app.name'),
            $body,
            [['Name' => $this->fullName, 'Email' => $this->Email]]
        )->send(true);
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
        // User must be explicitly active AND have a verified email
        return (bool)$this->IsActive && !is_null($this->EmailVerifiedOn);
    }

    public function isApproved(): bool
    {
        // Treat null as false, 1/true as true
        // return (bool) $this->IsApproved;
        return true;
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

    public function genderDetail(): BelongsTo
    {
        return $this->belongsTo(CodeDetail::class, 'Gender', 'ID');
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->Email;
    }

    public function sendPasswordResetNotification($token): void
    {
        $baseUrl = config('app.nextauth_url') ?? config('app.frontend_url') ?? config('app.url');
        $url = $baseUrl . '/reset-password?token=' . $token . '&email=' . urlencode($this->Email);

        $subject = 'Reset Password Notification';
        $body = "
            <h2>Hello {$this->FirstName},</h2>
            <p>You are receiving this email because we received a password reset request for your account.</p>
            <p><a href='{$url}' style='background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a></p>
            <p>If you did not request a password reset, no further action is required.</p>
            <p>Regards,<br>" . config('app.name') . "</p>
            <p><small>If you're having trouble clicking the \"Reset Password\" button, copy and paste the URL below into your web browser: <a href='{$url}'>{$url}</a></small></p>
        ";

        // System actor as sender
        $actor = User::find(1);

        if ($actor) {
            CRMEmailService::createRaw(
                $actor,
                $subject,
                $body,
                [['Name' => $this->Email]], // To array
                'ThirdPartyUser',
                (string)$this->Id,
                [], // cc
                [], // bcc
                EmailPriorityEnum::Important
            )->send(true); // Send immediately
        } else {
            // Fallback to default notification if admin user not found (or log error)
            $this->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
        }
    }
}
