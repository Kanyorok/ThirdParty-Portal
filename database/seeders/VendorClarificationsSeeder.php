<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VendorClarificationsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now(); // Current timestamp: 2025-05-16 08:59:00 AM EAT

        DB::table('t_VendorClarifications')->insert([
            [
                'TenderId' => 10, // Assumes tender ID 1 exists in t_Tenders
                'VendorId' => 2, // Assumes supplier ID 1 exists in t_Suppliers
                'Question' => 'What is the warranty period for the laptops?',
                'QuestionDate' => Carbon::today()->subDays(5), // 2025-05-11
                'Answer' => null,
                'AnswerDate' => null,
                'ISPUBLISHEDTOALL' => false,
                'CreatedBy' => 1, // Assumes user ID 1 exists in t_Users
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'TenderId' => 11, // Assumes tender ID 2 exists in t_Tenders
                'VendorId' => 3, // Assumes supplier ID 2 exists in t_Suppliers
                'Question' => 'Can you provide the detailed safety requirements for the construction project?',
                'QuestionDate' => Carbon::today()->subDays(7), // 2025-05-09
                'Answer' => null,
                'AnswerDate' => null,
                'ISPUBLISHEDTOALL' => false,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'TenderId' => 12, // Assumes tender ID 3 exists in t_Tenders
                'VendorId' => 4, // Assumes supplier ID 3 exists in t_Suppliers
                'Question' => 'What is the expected timeline for the IT system upgrade consultancy?',
                'QuestionDate' => Carbon::today()->subDays(2), // 2025-05-14
                'Answer' => null,
                'AnswerDate' => null,
                'ISPUBLISHEDTOALL' => false,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
