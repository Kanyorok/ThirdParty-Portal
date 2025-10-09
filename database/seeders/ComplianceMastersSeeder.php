<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceMastersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Regulatory Bodies
        DB::table('t_RegulatoryBodies')->insert([
            ['Name' => 'Central Bank of Kenya (CBK)', 'Jurisdiction' => 'Banking & Financial Services', 'IsActive' => 1],
            ['Name' => 'Capital Markets Authority (CMA)', 'Jurisdiction' => 'Capital Markets', 'IsActive' => 1],
            ['Name' => 'Kenya Revenue Authority (KRA)', 'Jurisdiction' => 'Tax Compliance', 'IsActive' => 1],
            ['Name' => 'NSSF', 'Jurisdiction' => 'Pensions & Social Security', 'IsActive' => 1],
            ['Name' => 'Data Protection Office', 'Jurisdiction' => 'Data Privacy', 'IsActive' => 1],
        ]);

        // 2. Compliance Areas
        DB::table('t_ComplianceAreas')->insert([
            ['Name' => 'AML (Anti-Money Laundering)', 'IsActive' => 1],
            ['Name' => 'KYC (Know Your Customer)', 'IsActive' => 1],
            ['Name' => 'Data Privacy', 'IsActive' => 1],
            ['Name' => 'Capital Adequacy', 'IsActive' => 1],
            ['Name' => 'Tax Reporting', 'IsActive' => 1],
            ['Name' => 'IFRS Compliance', 'IsActive' => 1],
        ]);

        // 3. Control Types
        DB::table('t_ControlTypes')->insert([
            ['Name' => 'Policy'],
            ['Name' => 'Internal Control'],
            ['Name' => 'Monitoring Point'],
            ['Name' => 'SOP (Procedure)'],
        ]);

        // 4. Incident Severity Levels
        DB::table('t_IncidentSeverityLevels')->insert([
            ['Name' => 'Low'],
            ['Name' => 'Medium'],
            ['Name' => 'High'],
            ['Name' => 'Critical'],
        ]);

        // 5. Filing Types
        DB::table('t_FilingTypes')->insert([
            ['Name' => 'CBK Return'],
            ['Name' => 'AML Return'],
            ['Name' => 'Tax Return'],
            ['Name' => 'CMA Disclosure'],
        ]);

        // 6. File Formats
        DB::table('t_FileFormats')->insert([
            ['Name' => 'Excel', 'MimeType' => 'application/vnd.ms-excel'],
            ['Name' => 'XBRL', 'MimeType' => 'application/xbrl+xml'],
            ['Name' => 'XML', 'MimeType' => 'application/xml'],
            ['Name' => 'PDF', 'MimeType' => 'application/pdf'],
        ]);

        // 7. Policy Categories
        DB::table('t_PolicyCategories')->insert([
            ['Name' => 'AML Policy'],
            ['Name' => 'Data Privacy Policy'],
            ['Name' => 'Cybersecurity Policy'],
            ['Name' => 'HR Conduct Policy'],
            ['Name' => 'Finance & Accounting Policy'],
        ]);

        // 8. Training Types
        DB::table('t_TrainingTypes')->insert([
            ['Name' => 'AML Awareness Training'],
            ['Name' => 'Data Protection Training'],
            ['Name' => 'Cybersecurity Training'],
            ['Name' => 'Compliance Officer Certification'],
        ]);
    }
}
