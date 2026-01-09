<?php

namespace Database\Seeders;

use App\Models\Core\Branch;
use App\Models\HR\Applicant;
use App\Models\HR\JobApplication;
use App\Models\HR\JobInterview;
use App\Models\HR\JobOffer;
use App\Models\HR\JobOpening;
use App\Models\HR\JobRequisition;
use App\Models\HR\OnboardingQueue;
use App\Models\HR\OnboardingTask;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use App\Models\HRM\Department;
use Illuminate\Database\Seeder;

class RecruitmentSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::whereNull('DeletedOn')->first();
        $department = Department::whereNull('DeletedOn')->first();
        $grade = JobGrade::where('IsActive', 1)->first();
        $role = JobRole::where('IsActive', 1)->first();

        $requisition = JobRequisition::create([
            'Code' => 'REQ-001',
            'Title' => 'ICT Support Officer',
            'DepartmentID' => $department?->Id,
            'BranchID' => $branch?->Id,
            'GradeID' => $grade?->Id,
            'RoleID' => $role?->Id,
            'EmploymentType' => 'Permanent',
            'ContractType' => 'Permanent',
            'Vacancies' => 1,
            'Priority' => 'High',
            'Justification' => 'Additional support capacity required.',
            'Status' => 'Approved',
            'RequestedBy' => 1,
            'RequestedOn' => now(),
            'ApprovedBy' => 1,
            'ApprovedOn' => now(),
            'CreatedBy' => 1,
            'CreatedOn' => now(),
        ]);

        $opening = JobOpening::create([
            'RequisitionID' => $requisition->Id,
            'Code' => 'JOB-001',
            'Title' => 'ICT Support Officer',
            'DepartmentID' => $department?->Id,
            'BranchID' => $branch?->Id,
            'GradeID' => $grade?->Id,
            'RoleID' => $role?->Id,
            'EmploymentType' => 'Permanent',
            'ContractType' => 'Permanent',
            'Vacancies' => 1,
            'Description' => 'Handle ICT support tickets and branch infrastructure.',
            'Requirements' => 'Diploma in IT, 2 years experience.',
            'Status' => 'Open',
            'PublishedOn' => now(),
            'CloseDate' => now()->addDays(21),
            'CreatedBy' => 1,
            'CreatedOn' => now(),
        ]);

        $applicant = Applicant::create([
            'FirstName' => 'Jane',
            'LastName' => 'Wanjiku',
            'Email' => 'jane.wanjiku@example.com',
            'Phone' => '254700000111',
            'Gender' => 'Female',
            'Source' => 'Referral',
            'CreatedBy' => 1,
            'CreatedOn' => now(),
        ]);

        $application = JobApplication::create([
            'JobOpeningID' => $opening->Id,
            'ApplicantID' => $applicant->Id,
            'Status' => 'Interview',
            'ExpectedSalary' => 65000,
            'NoticePeriodDays' => 30,
            'CreatedBy' => 1,
            'CreatedOn' => now(),
        ]);

        JobInterview::create([
            'ApplicationID' => $application->Id,
            'InterviewType' => 'Panel',
            'InterviewDate' => now()->addDays(3),
            'Panel' => 'HR, ICT Manager',
            'Location' => 'Head Office',
            'Status' => 'Scheduled',
            'CreatedBy' => 1,
            'CreatedOn' => now(),
        ]);

        $offer = JobOffer::create([
            'ApplicationID' => $application->Id,
            'OfferDate' => now()->addDays(5),
            'SalaryOffered' => 70000,
            'Benefits' => 'Medical cover, Airtime allowance',
            'Status' => 'Approved',
            'ApprovedBy' => 1,
            'ApprovedOn' => now(),
            'CreatedBy' => 1,
            'CreatedOn' => now(),
        ]);

        $queue = OnboardingQueue::create([
            'OfferID' => $offer->Id,
            'ApplicationID' => $application->Id,
            'CandidateName' => 'Jane Wanjiku',
            'Status' => 'Pending',
            'StartDate' => now()->addDays(14),
            'CreatedBy' => 1,
            'CreatedOn' => now(),
        ]);

        foreach ([
            'Collect identification documents',
            'Sign contract and offer letter',
            'Setup payroll profile',
        ] as $task) {
            OnboardingTask::create([
                'OnboardingID' => $queue->Id,
                'Title' => $task,
                'IsRequired' => 1,
                'Status' => 'Pending',
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ]);
        }
    }
}
