<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExitSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $actor = 1;

        $legalRefs = [
            ['Code' => 'SEC35', 'Section' => 'Section 35', 'Title' => 'Notice Periods', 'Description' => 'Notice periods for termination or resignation.'],
            ['Code' => 'SEC36', 'Section' => 'Section 36', 'Title' => 'Pay in Lieu of Notice', 'Description' => 'Payment in lieu of notice.'],
            ['Code' => 'SEC40', 'Section' => 'Section 40', 'Title' => 'Redundancy Procedure', 'Description' => 'Redundancy requirements and notifications.'],
            ['Code' => 'SEC41', 'Section' => 'Section 41', 'Title' => 'Hearing Before Termination', 'Description' => 'Right to be heard before termination.'],
            ['Code' => 'SEC44', 'Section' => 'Section 44', 'Title' => 'Summary Dismissal', 'Description' => 'Summary dismissal for gross misconduct.'],
            ['Code' => 'SEC49', 'Section' => 'Section 49', 'Title' => 'Terminal Dues', 'Description' => 'Terminal benefits and remedies.'],
            ['Code' => 'SEC51', 'Section' => 'Section 51', 'Title' => 'Certificate of Service', 'Description' => 'Certificate of service requirement.'],
        ];

        foreach ($legalRefs as $ref) {
            DB::table('t_HRExitLegalRefs')->updateOrInsert(
                ['Code' => $ref['Code']],
                array_merge($ref, ['IsActive' => 1, 'CreatedOn' => $now])
            );
        }

        $exitTypes = [
            ['Code' => 'RESIGN', 'Name' => 'Resignation', 'IsEmployerInitiated' => 0],
            ['Code' => 'RETIRE', 'Name' => 'Retirement', 'IsEmployerInitiated' => 0],
            ['Code' => 'CONEXP', 'Name' => 'Contract Expiry', 'IsEmployerInitiated' => 1],
            ['Code' => 'TERM', 'Name' => 'Termination', 'IsEmployerInitiated' => 1, 'RequiresCase' => 1, 'RequiresHearing' => 1],
            ['Code' => 'SUMMARY', 'Name' => 'Summary Dismissal', 'IsEmployerInitiated' => 1, 'RequiresCase' => 1, 'RequiresHearing' => 1, 'IsSummaryDismissal' => 1],
            ['Code' => 'REDUND', 'Name' => 'Redundancy', 'IsEmployerInitiated' => 1, 'IsRedundancy' => 1],
            ['Code' => 'DEATH', 'Name' => 'Death', 'IsEmployerInitiated' => 1],
            ['Code' => 'MED', 'Name' => 'Medical Incapacity', 'IsEmployerInitiated' => 1],
        ];

        foreach ($exitTypes as $type) {
            DB::table('t_HRExitTypes')->updateOrInsert(
                ['Code' => $type['Code']],
                array_merge([
                    'Description' => null,
                    'RequiresCase' => 0,
                    'RequiresHearing' => 0,
                    'IsRedundancy' => 0,
                    'IsSummaryDismissal' => 0,
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                ], $type)
            );
        }

        $deptMap = DB::table('t_Departments')
            ->whereNull('DeletedOn')
            ->pluck('Id', 'Name')
            ->toArray();

        $clearanceDepartments = [
            ['Name' => 'Line Manager', 'Sequence' => 1],
            ['Name' => 'IT', 'Sequence' => 2],
            ['Name' => 'Finance', 'Sequence' => 3],
            ['Name' => 'Admin', 'Sequence' => 4],
            ['Name' => 'HR', 'Sequence' => 5],
        ];

        foreach ($clearanceDepartments as $department) {
            DB::table('t_HRExitClearanceDepartments')->updateOrInsert(
                ['Name' => $department['Name']],
                array_merge($department, [
                    'DepartmentID' => $deptMap[$department['Name']] ?? null,
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                ])
            );
        }

        DB::table('t_HRExitChecklistTemplates')->updateOrInsert(
            ['Name' => 'Default Exit Checklist'],
            [
                'Description' => 'Default checklist for employee exit clearance.',
                'IsActive' => 1,
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
            ]
        );
        $templateId = DB::table('t_HRExitChecklistTemplates')->where('Name', 'Default Exit Checklist')->value('Id');

        $deptMap = DB::table('t_HRExitClearanceDepartments')
            ->pluck('Id', 'Name');

        $items = [
            ['ItemName' => 'Handover to line manager', 'ClearanceDepartmentID' => $deptMap['Line Manager'] ?? null, 'Sequence' => 1],
            ['ItemName' => 'Return IT assets & access', 'ClearanceDepartmentID' => $deptMap['IT'] ?? null, 'Sequence' => 2],
            ['ItemName' => 'Clear staff loans/advances', 'ClearanceDepartmentID' => $deptMap['Finance'] ?? null, 'Sequence' => 3],
            ['ItemName' => 'Return keys & admin assets', 'ClearanceDepartmentID' => $deptMap['Admin'] ?? null, 'Sequence' => 4],
            ['ItemName' => 'Exit interview completed', 'ClearanceDepartmentID' => $deptMap['HR'] ?? null, 'Sequence' => 5],
        ];

        foreach ($items as $item) {
            DB::table('t_HRExitChecklistItems')->updateOrInsert(
                ['TemplateID' => $templateId, 'ItemName' => $item['ItemName']],
                array_merge($item, [
                    'TemplateID' => $templateId,
                    'IsMandatory' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                ])
            );
        }

        DB::table('t_HRExitPolicies')->updateOrInsert(
            ['Name' => 'Default Exit Policy'],
            [
                'EffectiveFrom' => $now->toDateString(),
                'EmploymentTypes' => 'Permanent,Contract,Casual',
                'ContractTypes' => null,
                'ApprovalWorkflow' => 'HR -> Manager',
                'RedundancyCriteria' => 'LIFO / performance / skills',
                'TerminalDuesConfig' => 'Salary to last day, leave encashment, notice pay, gratuity, deductions',
                'ChecklistTemplateID' => $templateId,
                'AllowNoticePay' => 1,
                'AllowNoticeWaiver' => 1,
                'IsActive' => 1,
                'CreatedBy' => $actor,
                'CreatedOn' => $now,
            ]
        );

        $policyId = DB::table('t_HRExitPolicies')->where('Name', 'Default Exit Policy')->value('Id');
        if ($policyId) {
            DB::table('t_HRExitPolicyNoticePeriods')->where('PolicyID', $policyId)->delete();
            $noticePeriods = [
                ['EmploymentType' => 'Permanent', 'ContractType' => null, 'NoticeDays' => 30, 'PayInLieuAllowed' => 1],
                ['EmploymentType' => 'Contract', 'ContractType' => null, 'NoticeDays' => 14, 'PayInLieuAllowed' => 1],
                ['EmploymentType' => 'Casual', 'ContractType' => null, 'NoticeDays' => 7, 'PayInLieuAllowed' => 1],
            ];
            foreach ($noticePeriods as $period) {
                DB::table('t_HRExitPolicyNoticePeriods')->insert([
                    'PolicyID' => $policyId,
                    'EmploymentType' => $period['EmploymentType'],
                    'ContractType' => $period['ContractType'],
                    'NoticeDays' => $period['NoticeDays'],
                    'PayInLieuAllowed' => $period['PayInLieuAllowed'],
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                ]);
            }
        }

        $letterTypes = [
            'ResignationAcknowledgement',
            'TerminationNotice',
            'RedundancyNotice',
            'ReleaseLetter',
            'CertificateOfService',
            'FinalSettlement',
        ];

        foreach ($letterTypes as $type) {
            DB::table('t_HRExitLetterTemplates')->updateOrInsert(
                ['LetterType' => $type],
                ['TemplateID' => null, 'IsActive' => 1, 'CreatedBy' => $actor, 'CreatedOn' => $now]
            );
        }

        $exitQuestions = [
            ['Question' => 'What is the main reason for your departure?', 'Sequence' => 1],
            ['Question' => 'What did you enjoy most about your role?', 'Sequence' => 2],
            ['Question' => 'What could we improve to retain employees?', 'Sequence' => 3],
            ['Question' => 'How would you rate your relationship with your supervisor?', 'Sequence' => 4],
            ['Question' => 'Would you consider returning in the future?', 'Sequence' => 5],
        ];

        foreach ($exitQuestions as $question) {
            DB::table('t_HRExitInterviewQuestions')->updateOrInsert(
                ['Question' => $question['Question']],
                array_merge($question, ['IsActive' => 1, 'CreatedBy' => $actor, 'CreatedOn' => $now])
            );
        }
    }
}
