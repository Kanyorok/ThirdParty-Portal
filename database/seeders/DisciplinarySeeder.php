<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisciplinarySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $legalRefs = [
            ['Code' => 'SEC41', 'Section' => 'Section 41', 'Title' => 'Notification & Hearing', 'Description' => 'Employee must be informed of allegations and given a hearing before disciplinary action.'],
            ['Code' => 'SEC43', 'Section' => 'Section 43', 'Title' => 'Proof of Reason', 'Description' => 'Employer must prove the reason for termination.'],
            ['Code' => 'SEC44', 'Section' => 'Section 44', 'Title' => 'Summary Dismissal', 'Description' => 'Gross misconduct may justify summary dismissal.'],
            ['Code' => 'SEC45', 'Section' => 'Section 45', 'Title' => 'Procedural & Substantive Fairness', 'Description' => 'Termination must be fair in both procedure and reason.'],
            ['Code' => 'SEC47', 'Section' => 'Section 47', 'Title' => 'Burden of Proof', 'Description' => 'Burden of proof in disputes lies with employer and employee.'],
        ];

        foreach ($legalRefs as $ref) {
            DB::table('t_HRDisciplinaryLegalRefs')->updateOrInsert(
                ['Code' => $ref['Code']],
                array_merge($ref, ['IsActive' => 1, 'CreatedOn' => $now])
            );
        }

        DB::table('t_HRDisciplinaryPolicies')->updateOrInsert(
            ['Name' => 'Default Disciplinary Policy'],
            [
                'EffectiveFrom' => $now->toDateString(),
                'EmploymentTypes' => 'Permanent,Contract,Casual',
                'ContractTypes' => null,
                'ProgressiveRules' => 'Verbal warning -> Written warning -> Final warning -> Suspension/Termination',
                'AppealDeadlineDays' => 7,
                'RetentionMonths' => 24,
                'AllowDirectHearing' => 0,
                'IsActive' => 1,
                'CreatedOn' => $now,
            ]
        );

        $sanctions = [
            ['Code' => 'VERBAL', 'Name' => 'Verbal Warning', 'Description' => 'First warning', 'AffectsPayroll' => 0],
            ['Code' => 'WRITTEN', 'Name' => 'Written Warning', 'Description' => 'Formal written warning', 'AffectsPayroll' => 0],
            ['Code' => 'FINAL', 'Name' => 'Final Warning', 'Description' => 'Final warning before stronger action', 'AffectsPayroll' => 0],
            ['Code' => 'SUS_WP', 'Name' => 'Suspension With Pay', 'Description' => 'Suspension with pay', 'IsSuspension' => 1, 'SuspensionWithoutPay' => 0, 'AffectsPayroll' => 0],
            ['Code' => 'SUS_NOPAY', 'Name' => 'Suspension Without Pay', 'Description' => 'Suspension without pay', 'IsSuspension' => 1, 'SuspensionWithoutPay' => 1, 'AffectsPayroll' => 1],
            ['Code' => 'DEMO', 'Name' => 'Demotion', 'Description' => 'Demotion to lower role/grade', 'AffectsPayroll' => 0, 'UpdatesEmploymentStatus' => 0],
            ['Code' => 'TERM', 'Name' => 'Termination', 'Description' => 'Employment termination', 'AffectsPayroll' => 0, 'UpdatesEmploymentStatus' => 1, 'EmploymentStatus' => 'Terminated'],
            ['Code' => 'SUMMARY', 'Name' => 'Summary Dismissal', 'Description' => 'Summary dismissal for gross misconduct', 'AffectsPayroll' => 0, 'UpdatesEmploymentStatus' => 1, 'EmploymentStatus' => 'Dismissed'],
        ];

        foreach ($sanctions as $sanction) {
            DB::table('t_HRDisciplinarySanctions')->updateOrInsert(
                ['Code' => $sanction['Code']],
                array_merge([
                    'IsSuspension' => 0,
                    'SuspensionWithoutPay' => 0,
                    'AffectsPayroll' => 0,
                    'BlocksLeave' => 0,
                    'UpdatesEmploymentStatus' => 0,
                    'IsActive' => 1,
                    'CreatedOn' => $now,
                ], $sanction)
            );
        }

        $categories = [
            ['Name' => 'Conduct', 'Description' => 'General misconduct and ethics'],
            ['Name' => 'Performance', 'Description' => 'Performance related offences'],
            ['Name' => 'Attendance', 'Description' => 'Absenteeism, lateness, or time offences'],
        ];

        foreach ($categories as $category) {
            DB::table('t_HRDisciplinaryOffenceCategories')->updateOrInsert(
                ['Name' => $category['Name']],
                array_merge($category, ['IsActive' => 1, 'CreatedOn' => $now])
            );
        }

        $catConduct = DB::table('t_HRDisciplinaryOffenceCategories')->where('Name', 'Conduct')->value('Id');
        $catAttendance = DB::table('t_HRDisciplinaryOffenceCategories')->where('Name', 'Attendance')->value('Id');
        $catPerformance = DB::table('t_HRDisciplinaryOffenceCategories')->where('Name', 'Performance')->value('Id');
        $sanctionWritten = DB::table('t_HRDisciplinarySanctions')->where('Code', 'WRITTEN')->value('Id');
        $sanctionFinal = DB::table('t_HRDisciplinarySanctions')->where('Code', 'FINAL')->value('Id');
        $sanctionSummary = DB::table('t_HRDisciplinarySanctions')->where('Code', 'SUMMARY')->value('Id');

        $offences = [
            ['Code' => 'MIN_ATT', 'Name' => 'Late reporting', 'Severity' => 'Minor', 'CategoryID' => $catAttendance, 'RecommendedSanctionID' => $sanctionWritten, 'HearingRequired' => 0, 'RequiresEvidence' => 0],
            ['Code' => 'MAJ_PERF', 'Name' => 'Consistent underperformance', 'Severity' => 'Major', 'CategoryID' => $catPerformance, 'RecommendedSanctionID' => $sanctionFinal, 'HearingRequired' => 1, 'RequiresEvidence' => 1],
            ['Code' => 'GROSS_MIS', 'Name' => 'Gross misconduct', 'Severity' => 'Gross', 'CategoryID' => $catConduct, 'RecommendedSanctionID' => $sanctionSummary, 'HearingRequired' => 1, 'SummaryDismissalAllowed' => 1, 'RequiresEvidence' => 1, 'RequiresApproval' => 1],
        ];

        foreach ($offences as $offence) {
            DB::table('t_HRDisciplinaryOffences')->updateOrInsert(
                ['Code' => $offence['Code']],
                array_merge([
                    'HearingRequired' => 0,
                    'SummaryDismissalAllowed' => 0,
                    'RequiresEvidence' => 0,
                    'RequiresApproval' => 0,
                    'IsActive' => 1,
                    'CreatedOn' => $now,
                ], $offence)
            );
        }

        $letterTypes = ['ShowCause', 'HearingNotice', 'Decision', 'AppealOutcome'];
        foreach ($letterTypes as $type) {
            DB::table('t_HRDisciplinaryLetterTemplates')->updateOrInsert(
                ['LetterType' => $type],
                ['TemplateID' => null, 'IsActive' => 1, 'CreatedOn' => $now]
            );
        }
    }
}
