<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VendorClarifications;
use Carbon\Carbon;

class VendorClarificationsSeeder extends Seeder
{
    public function run(): void
    {
        $sampleData = [
            [
                'TenderID' => 1,
                'VendorID' => 1,
                'Question' => 'Do we need to include delivery timelines in our pricing?',
                'QuestionDate' => Carbon::now()->subDays(5),
                'Answer' => null,
                'AnswerDate' => null,
                'ISPUBLISHEDTOALL' => false,
            ],
            [
                'TenderID' => 1,
                'VendorID' => 2,
                'Question' => 'Can we submit additional certifications with our bid?',
                'QuestionDate' => Carbon::now()->subDays(3),
                'Answer' => 'Yes, additional certifications are welcome.',
                'AnswerDate' => Carbon::now()->subDays(1),
                'ISPUBLISHEDTOALL' => true,
            ],
            [
                'TenderID' => 2,
                'VendorID' => 1,
                'Question' => 'What is the expected timeline for the project?',
                'QuestionDate' => Carbon::now()->subDays(2),
                'Answer' => null,
                'AnswerDate' => null,
                'ISPUBLISHEDTOALL' => false,
            ],
        ];

        foreach ($sampleData as $data) {
            VendorClarifications::create($data);
        }
    }
}