<?php

namespace App\Services\HR;

use App\Enums\EmailPriorityEnum;
use App\Models\Auth\User;
use App\Models\HR\Employee;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(public User $user)
    {
    }

    /**
     * Create a user account for an employee
     */
    public static function createForEmployee(Employee $employee, User $actor): self
    {
        // Check if user already exists
        $existingUser = User::where('EmployeeId', $employee->Id)->first();

        if ($existingUser) {
            // If user exists but was deleted, restore it
            if ($existingUser->trashed()) {
                $existingUser->forceFill([
                    'Name' => self::getFullName($employee),
                    'Email' => $employee->Email,
                    'Phone' => $employee->Phone,
                    'Password' => bcrypt(Str::random(10)),
                    'ModifiedBy' => $actor->Id,
                    'ModifiedOn' => now(),
                    'DeletedOn' => null,
                    'DeletedBy' => null,
                ])->save();

                return new self($existingUser);
            }

            return new self($existingUser);
        }

        // Generate UserID
        $userId = self::generateUserID($employee->FirstName, $employee->LastName);

        // Create new user
        $user = User::create([
            'UserID' => $userId,
            'Name' => self::getFullName($employee),
            'Email' => $employee->Email,
            'Phone' => $employee->Phone,
            'EmployeeId' => $employee->Id,
            'BranchId' => $employee->BranchID,
            'Linked' => false,
            'Password' => bcrypt(Str::random(10)),
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
        ]);

        activity()
            ->causedBy($actor)
            ->performedOn($user)
            ->event('create')
            ->log("Created user account {$user->UserID} for employee {$employee->EmployeeNo}");

        return new self($user);
    }

    /**
     * Generate UserID from employee name
     * Format: First letter of first name + full last name (uppercase, no spaces)
     * If exists, append numbers: JDOE, JDOE2, JDOE3, etc.
     */
    protected static function generateUserID(string $firstName, string $lastName): string
    {
        $baseId = strtoupper(
            substr($firstName, 0, 1) .
            str_replace([' ', '-', '_'], '', $lastName)
        );

        $userId = $baseId;
        $counter = 1;

        while (User::withTrashed()->where('UserID', $userId)->exists()) {
            $counter++;
            $userId = $baseId . $counter;
        }

        return $userId;
    }

    /**
     * Get full name from employee
     */
    protected static function getFullName(Employee $employee): string
    {
        $parts = array_filter([
            $employee->FirstName,
            $employee->OtherNames,
            $employee->LastName,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Send welcome email with password reset link
     */
    public function sendWelcomeEmail(): self
    {
        // Only send if user was recently created (within last 2 days)
        if (now()->subDays(2)->startOfDay()->greaterThan($this->user->CreatedOn)) {
            return $this;
        }

        $resetUrl = $this->createPasswordResetUrl();
        $appName = config('app.name');

        $subject = "Welcome to {$appName}";
        $body = "
            <div>
                <p>Hello {$this->user->Name},</p>
                <p>An account has been created for you in {$appName}.</p>
                <p>Your UserID is: <strong>{$this->user->UserID}</strong></p>
                <p>Please click the link below to set your password:</p>
                <p><a href='{$resetUrl}'>Set Your Password</a></p>
                <p>This link will expire in 60 minutes.</p>
                <p>If you have any questions, please contact your system administrator.</p>
            </div>
        ";

        // Use the CRM email service if available
        if (class_exists('\App\Services\CRMEmailService')) {
            try {
                \App\Services\CRMEmailService::createUser(
                    $this->user,
                    $subject,
                    $body,
                    $this->user, // actor
                    [], // cc
                    EmailPriorityEnum::Important
                )->send(false); // Queue the email
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send welcome email: " . $e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Create password reset URL
     */
    protected function createPasswordResetUrl(): string
    {
        return url(route('password.reset', [
            'token' => Password::createToken($this->user),
            'email' => $this->user->Email,
        ], false));
    }

    /**
     * Send password reset notification
     */
    public function sendPasswordResetNotification(): self
    {
        $resetUrl = $this->createPasswordResetUrl();
        $subject = 'Reset Password Notification';
        $body = "
            <p>You are receiving this email because we received a password reset request for your account.</p>
            <p><a href='{$resetUrl}'>Reset Password</a></p>
            <p>This password reset link will expire in 60 minutes.</p>
            <p>If you did not request a password reset, no further action is required.</p>
        ";

        if (class_exists('\App\Services\CRMEmailService')) {
            try {
                \App\Services\CRMEmailService::createUser(
                    $this->user,
                    $subject,
                    $body,
                    $this->user,
                    [],
                    EmailPriorityEnum::Normal
                )->send(false);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send password reset email: " . $e->getMessage());
            }
        }

        return $this;
    }
}
