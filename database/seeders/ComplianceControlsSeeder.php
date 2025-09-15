<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceControlsSeeder extends Seeder
{
    public function run(): void
    {
        // Sample Compliance Controls
        DB::table('t_ComplianceControls')->insert([
            [
                'Title' => 'AML Transaction Monitoring',
                'Description' => 'Daily monitoring of transactions to detect unusual or suspicious activity.',
                'ComplianceAreaID' => 1, // AML (from ComplianceMastersSeeder)
                'ControlTypeID' => 2, // Internal Control
                'OwnerID' => 1, // Assume user with Id=1 exists
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'Title' => 'KYC Documentation Review',
                'Description' => 'Ensure all new customer accounts have complete and verified KYC documents.',
                'ComplianceAreaID' => 2, // KYC
                'ControlTypeID' => 4, // SOP (Procedure)
                'OwnerID' => 2,
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'Title' => 'Data Privacy Breach Response',
                'Description' => 'Procedure for handling and reporting data breaches.',
                'ComplianceAreaID' => 3, // Data Privacy
                'ControlTypeID' => 1, // Policy
                'OwnerID' => 1,
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
        ]);

        // Obligations linkages (assuming obligations exist in t_ComplianceObligations)
        DB::table('t_ComplianceControlObligations')->insert([
            ['ControlID' => 1, 'ObligationID' => 1],
            ['ControlID' => 1, 'ObligationID' => 2],
            ['ControlID' => 2, 'ObligationID' => 3],
            ['ControlID' => 3, 'ObligationID' => 4],
        ]);

        // Evidence (sample placeholder files)
        DB::table('t_ComplianceControlEvidence')->insert([
            [
                'ControlID' => 1,
                'FileName' => 'aml_monitoring_report.pdf',
                'MimeType' => 'application/pdf',
                'FilePath' => 'compliance/controls/aml_monitoring_report.pdf',
                'Version' => 1,
                'UploadedBy' => 1,
                'UploadedOn' => now(),
            ],
            [
                'ControlID' => 2,
                'FileName' => 'kyc_checklist.xlsx',
                'MimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'FilePath' => 'compliance/controls/kyc_checklist.xlsx',
                'Version' => 1,
                'UploadedBy' => 1,
                'UploadedOn' => now(),
            ],
            [
                'ControlID' => 3,
                'FileName' => 'data_privacy_breach_policy.docx',
                'MimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'FilePath' => 'compliance/controls/data_privacy_breach_policy.docx',
                'Version' => 1,
                'UploadedBy' => 1,
                'UploadedOn' => now(),
            ],
        ]);
    }
}