<?php

namespace App\Services;

use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class RegistrationService
{
    public function registerUser(array $data): ThirdPartyUser
    {
        return DB::transaction(function () use ($data) {
            $user = ThirdPartyUser::create([
                'FirstName' => $data['FirstName'],
                'LastName' => $data['LastName'],
                'Email' => $data['Email'],
                'Phone' => $data['Phone'],
                'Password' => Hash::make($data['Password']),
                'CreatedBy' => null,
            ]);

            event(new Registered($user));

            return $user;
        });
    }
}
