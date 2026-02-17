<?php

namespace Database\Seeders;

use App\Models\HR\TrainingCategory;
use App\Models\HR\TrainingProgram;
use App\Models\HR\TrainingSession;
use App\Models\HR\TrainingTrainer;
use Illuminate\Database\Seeder;

class HRTrainingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $actor = 1;

        $categories = [
            ['Code' => 'ONB', 'Name' => 'Onboarding', 'Description' => 'Onboarding programs.'],
            ['Code' => 'COMP', 'Name' => 'Compliance', 'Description' => 'Compliance and regulatory training.'],
            ['Code' => 'TECH', 'Name' => 'Technical', 'Description' => 'Technical skill training.'],
            ['Code' => 'SOFT', 'Name' => 'Soft Skills', 'Description' => 'Soft skills and customer service.'],
            ['Code' => 'LEAD', 'Name' => 'Leadership', 'Description' => 'Leadership development.'],
            ['Code' => 'REG', 'Name' => 'Regulatory', 'Description' => 'Regulatory training programs.'],
        ];

        foreach ($categories as $category) {
            TrainingCategory::updateOrCreate(
                ['Code' => $category['Code']],
                array_merge($category, [
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                ])
            );
        }

        $trainer = TrainingTrainer::firstOrCreate(
            ['Name' => 'External Facilitator'],
            [
                'TrainerType' => 'External',
                'Email' => 'training@example.com',
                'Phone' => '0700000000',
                'Expertise' => 'Onboarding & compliance',
                'IsActive' => 1,
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
            ]
        );

        $categoryId = TrainingCategory::where('Code', 'ONB')->value('Id');
        $program = TrainingProgram::updateOrCreate(
            ['Code' => 'ONB-101'],
            [
                'Title' => 'HR Onboarding Basics',
                'CategoryID' => $categoryId,
                'DeliveryMode' => 'Classroom',
                'DurationHours' => 4,
                'Objectives' => 'Introduce policies, systems, and culture.',
                'IsMandatory' => 1,
                'HasCertification' => 1,
                'Status' => 'Active',
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
            ]
        );

        TrainingSession::updateOrCreate(
            ['SessionCode' => 'ONB-2026-01'],
            [
                'ProgramID' => $program->Id,
                'Title' => 'January Onboarding Session',
                'StartDate' => $now->toDateString(),
                'EndDate' => $now->toDateString(),
                'StartTime' => '09:00:00',
                'EndTime' => '13:00:00',
                'Location' => 'Head Office',
                'TrainerID' => $trainer->Id,
                'MaxParticipants' => 30,
                'Status' => 'Planned',
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
            ]
        );
    }
}
