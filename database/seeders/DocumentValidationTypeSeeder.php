<?php

namespace Database\Seeders;

use App\Helpers\SystemHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentValidationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actor = SystemHelper::user();
        $date = now();
        DB::table('t_DocumentValidationTypes')->insert([
            [
                "ValidationTypeId" => 'ValType001',
                "Name" => 'Client Onboarding',
                "Notes" => 'Client Onboarding',
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
                'CreatedOn' => $date,
                'ModifiedOn' => $date,
            ],
            [
                "ValidationTypeId" => 'ValType002',
                "Name" => 'Loan Application',
                "Notes" => 'Loan Applications',
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
                'CreatedOn' => $date,
                'ModifiedOn' => $date,
            ]
        ]);
    }
}
