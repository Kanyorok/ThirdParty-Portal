<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceObligationsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('t_ComplianceObligations')->insert([
            [
                'Title' => 'Daily AML Transaction Screening',
                'Description' => 'Screen transactions against AML watchlists daily.',
                'RegulatorID' => 1, // CBK
                'ComplianceAreaID' => 1, // AML
                'EffectiveDate' => now()->subYear(),
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'Title' => 'Customer KYC Verification',
                'Description' => 'All customer accounts must have verified KYC.',
                'RegulatorID' => 1, // CBK
                'ComplianceAreaID' => 2, // KYC
                'EffectiveDate' => now()->subYear(),
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'Title' => 'Quarterly Tax Reporting',
                'Description' => 'Submit tax compliance reports to KRA quarterly.',
                'RegulatorID' => 3, // KRA
                'ComplianceAreaID' => 5, // Tax Reporting
                'EffectiveDate' => now()->subMonths(6),
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'Title' => 'Data Breach Notification',
                'Description' => 'Report data breaches to Data Protection Office within 72 hours.',
                'RegulatorID' => 5, // Data Protection Office
                'ComplianceAreaID' => 3, // Data Privacy
                'EffectiveDate' => now()->subMonths(3),
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
        ]);
    }
}