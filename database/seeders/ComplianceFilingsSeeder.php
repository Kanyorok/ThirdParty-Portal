<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceFilingsSeeder extends Seeder
{
    public function run(): void
    {
        // Filing Templates
        DB::table('t_ComplianceFilingTemplates')->insert([
            [
                'Name' => 'Monthly CBK Prudential Return',
                'FilingTypeID' => 1, // CBK Return
                'RegulatorID' => 1, // CBK
                'FormatID' => 1, // Excel
                'Frequency' => 'Monthly',
                'DueDay' => '15th of every month',
                'PortalURL' => 'https://cbkreturns.centralbank.go.ke/',
                'Description' => 'Prudential return to CBK covering capital, liquidity and exposures.',
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'Name' => 'Quarterly AML Report',
                'FilingTypeID' => 2, // AML Return
                'RegulatorID' => 1, // CBK
                'FormatID' => 4, // PDF
                'Frequency' => 'Quarterly',
                'DueDay' => '15th after quarter end',
                'PortalURL' => 'https://cbkreturns.centralbank.go.ke/',
                'Description' => 'Quarterly AML compliance return submitted to CBK.',
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'Name' => 'Annual Tax Return',
                'FilingTypeID' => 3, // Tax Return
                'RegulatorID' => 3, // KRA
                'FormatID' => 3, // XML
                'Frequency' => 'Annually',
                'DueDay' => '30th June',
                'PortalURL' => 'https://itax.kra.go.ke/KRA-Portal/',
                'Description' => 'Annual corporate income tax return filed with KRA.',
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
        ]);

        // Sample Filings
        DB::table('t_ComplianceFilings')->insert([
            [
                'TemplateID' => 1,
                'SubmissionDate' => now()->subDays(15),
                'FileName' => 'prudential_return_july.xlsx',
                'MimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'FilePath' => 'compliance/filings/prudential_return_july.xlsx',
                'Status' => 'Submitted',
                'Notes' => 'Submitted on time, awaiting acknowledgment.',
                'SubmittedBy' => 2, // user id
                'CreatedOn' => now()->subDays(15),
            ],
            [
                'TemplateID' => 2,
                'SubmissionDate' => now()->subMonths(2),
                'FileName' => 'aml_q2_report.pdf',
                'MimeType' => 'application/pdf',
                'FilePath' => 'compliance/filings/aml_q2_report.pdf',
                'Status' => 'Accepted',
                'Notes' => 'Acknowledged by CBK.',
                'SubmittedBy' => 3,
                'CreatedOn' => now()->subMonths(2),
            ],
            [
                'TemplateID' => 3,
                'SubmissionDate' => now()->subMonths(7),
                'FileName' => 'tax_return_2024.xml',
                'MimeType' => 'application/xml',
                'FilePath' => 'compliance/filings/tax_return_2024.xml',
                'Status' => 'Rejected',
                'Notes' => 'Rejection due to missing schedules. Resubmission required.',
                'SubmittedBy' => 4,
                'CreatedOn' => now()->subMonths(7),
            ],
        ]);

        // Acknowledgment Files
        DB::table('t_ComplianceFilingAcknowledgments')->insert([
            [
                'FilingID' => 2,
                'AckFileName' => 'aml_ack_letter.pdf',
                'MimeType' => 'application/pdf',
                'FilePath' => 'compliance/acknowledgments/aml_ack_letter.pdf',
                'UploadedBy' => 1,
                'UploadedOn' => now()->subMonths(2)->addDays(2),
            ]
        ]);
    }
}