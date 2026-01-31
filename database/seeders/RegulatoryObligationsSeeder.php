<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegulatoryObligationsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('t_RegulatoryObligations')->insert([
            [
                'ObligationTitle' => 'Annual CBK Compliance Report',
                'ObligationDescription' => 'Submit yearly report to Central Bank as per Prudential Guidelines.',
                'RegulatoryBody' => 'CBK',
                'ObligationType' => 'Report Submission',
                'EffectiveDate' => now(),
                'DueDate' => now()->addMonths(2),
                'IsRecurring' => true,
                'RecurrenceType' => 'Yearly',
                'Status' => 'Pending',
                'ComplianceArea' => 'Regulatory Reporting',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ObligationTitle' => 'Quarterly AML Risk Assessment Filing',
                'ObligationDescription' => 'File AML risk assessment report to regulator.',
                'RegulatoryBody' => 'SASRA',
                'ObligationType' => 'AML Filing',
                'EffectiveDate' => now(),
                'DueDate' => now()->addMonths(1),
                'IsRecurring' => true,
                'RecurrenceType' => 'Quarterly',
                'Status' => 'Pending',
                'ComplianceArea' => 'AML/KYC',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
