<?php

namespace App\Services\BR;

use App\Models\Auth\User;
use App\Models\BR\BRUser;
use App\Models\ThirdParty\ThirdPartyUser;

class BREncryption
{
    public static function decrypt(string $cipher, string $keyOne, string $keyTwo = ''): ?string
    {
        $cmd = config('app.br.crypto');

        if (! file_exists($cmd) || ! is_executable($cmd)) {
            return null;
        }

        $cmd = config('app.br.crypto');
        $output = shell_exec("{$cmd} UnLionStr {$cipher} {$keyOne} {$keyTwo}");
        if (is_string($output) === false) {
            return null;
        }

        return trim($output);
    }

    public static function checkAuthBRUser(BRUser $user, #[\SensitiveParameter] string $password): bool
    {
        return self::isValid($user->OperatorID . $password, $user->Password);
    }

    public static function checkAuthUser(User|ThirdPartyUser $user, #[\SensitiveParameter] string $password): bool
    {
        return self::isValid($user->UserID . $password, $user->Password);
    }

    public static function hashUser(\App\Models\Auth\User|\App\Models\ThirdParty\ThirdPartyUser $user, #[\SensitiveParameter] string $password): string
    {
        $identifier = ($user instanceof \App\Models\Auth\User) ? $user->UserID : $user->Email;
        
        return self::_encryptText($identifier . $password);
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
