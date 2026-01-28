<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemHelper::user();

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
    }
}
