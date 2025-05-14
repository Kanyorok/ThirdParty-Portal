<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use App\Models\Auth\User;
use App\Services\BR\BREncryption;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actor = SystemHelper::user();

        // $brUser = BRUser::query()->where('OperatorID', 'CSADM')->with('client')->first();
        // if ($brUser instanceof BRUser) {
        //     User::create([
        //         'UserID' => $brUser->OperatorID,
        //         'Name' => $brUser->client->Name,
        //         'Email' => $brUser->client->Email ?? 'admin@test.co.ke',
        //         'Phone' => $brUser->client->Phone1 ?? '0700100100',
        //         'Password' => $brUser->Password,
        //         'BranchId' => '00',
        //         'Linked' => true,
        //         'CreatedBy' => $user->Id,
        //         'ModifiedBy' => $user->Id,
        //     ]);
        // }

       $user= User::create([
            'UserID' => 'CSADM',
            'Name' => 'Defualt User',
            'Email' => 'admin@test.co.ke',
            'Phone' => '0700100100',
            'Password' => "Nor set",
            'BranchId' => '00',
            'Linked' => false,
            'CreatedBy' => $actor->Id,
            'ModifiedBy' => $actor->Id,
        ]);

        $user->update([
            'Password' => BREncryption::hashUser($user,'123456')
        ]);


    }
}
