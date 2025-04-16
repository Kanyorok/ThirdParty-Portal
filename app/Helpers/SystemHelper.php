<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class SystemHelper
{
    public const ID = 'ERPSYS';

    public static function user(): User
    {
        return User::where('UserID', self::ID)->withTrashed()->firstOr(function () {
            return self::_create();
        });
    }

    protected static function _create(): User
    {
        self::notifyAdmin('new system account created ?');
        return User::create([
                             'UserID'          => self::ID,
                             'Name'            => 'SYSTEM',
                             'Email'           => 'SYSTEM ACCOUNT',
                             'Phone'           => 0,
                             'BranchId'        => '00',
                             'Linked'          => false,
                             'Notes'           => 'SYSTEM ACCOUNT',
                             'Password'        => 'SYSTEM ACCOUNT',
                             'Email_Signature' => '<p>Regards,<br>. .<br>' . config('org.name') . '</p>',
                            ]);
    }

    /**
     * Check if user is a system.
     */
    public static function isSystem(User $user): bool
    {
        return ($user->UserID === self::ID);
    }


    public static function notifyAdmin(string $message): void
    {
        Log::critical($message);
    }
}
