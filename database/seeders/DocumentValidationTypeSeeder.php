<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use App\Services\DMS\Verification\ValidationTypeService;
use Illuminate\Database\Seeder;

class DocumentValidationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actor = User::query()->where('UserID', 'CSADM')->first();
        if ($actor) {
            ValidationTypeService::create('Client Onboarding', $actor, 'Client Onboarding');
            ValidationTypeService::create('Loan Application', $actor, 'Loan Application');
        }

    }
}
