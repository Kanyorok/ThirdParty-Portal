<?php

namespace App\Services\BR;

use App\Models\BR\BRUser;
use App\Models\User;

class BREncryption
{
    public static function checkAuthBRUser(BRUser $user, #[\SensitiveParameter] string $password): bool
    {
        return self::isValid($user->OperatorID . $password, $user->Password);
    }

    public static function checkAuthUser(User $user, #[\SensitiveParameter] string $password): bool
    {
        return self::isValid($user->UserID . $password, $user->Password);
    }

    public static function hashUser(User $user, #[\SensitiveParameter] string $password): string
    {
        return self::_encryptText($user->UserID . $password);
    }


    private static function _encryptText(string $strInputText): string
    {
        return base64_encode(hash('sha256', $strInputText, true));
    }

    /**
     * Check if Valid
     */
    public static function isValid(string $strInputText, string $strHashString): bool
    {
        return (self::_encryptText($strInputText) === $strHashString);
    }
}
