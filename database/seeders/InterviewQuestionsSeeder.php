<?php

namespace Database\Seeders;

use App\Models\HR\JobInterviewQuestion;
use App\Models\HR\JobInterviewQuestionGroup;
use Illuminate\Database\Seeder;

class InterviewQuestionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $groups = [
            'HR & Compliance' => [
                'Why are you interested in this role?','Describe a time you resolved a workplace conflict.','How do you handle pressure and deadlines?','Tell us about a time you demonstrated integrity at work.',
            ],
            'Technical' => [
                'Describe a recent technical problem you solved end-to-end.','Which tools or systems do you use daily and why?','How do you ensure quality in your deliverables?','Explain a complex concept to a non-technical user.',
            ],
            'Customer & Service' => [
                'How do you handle a dissatisfied customer?','Give an example of going above and beyond for a client.','How do you prioritize competing service requests?',
            ],
            'Personal & Behavioral' => [
                'What are your key strengths and weaknesses?','Where do you see yourself in two years?','Describe a time you showed leadership without authority.',
            ],
        ];

        foreach ($groups as $groupName => $questions) {
            $group = JobInterviewQuestionGroup::firstOrCreate(
                ['Name' => $groupName],
                [
                    'Description' => null,
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                ]
            );

            foreach ($questions as $title) {
                JobInterviewQuestion::firstOrCreate(
                    ['Title' => $title],
                    [
                        'GroupID' => $group->Id,
                        'Guidance' => null,
                        'IsActive' => 1,
                        'CreatedBy' => 1,
                        'CreatedOn' => $now,
                    ]
                );
            }
        }
    }
}
