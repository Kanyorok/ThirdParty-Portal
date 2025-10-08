<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComplianceIncidentsSeeder extends Seeder
{
    public function run(): void
    {
        // Insert sample incidents
        DB::table('t_ComplianceIncidents')->insert([
            [
                'ObligationID' => 1, // Daily AML Screening
                'Title' => 'Missed Daily AML Screening',
                'Description' => 'The AML system was offline for 6 hours and screening did not run.',
                'IncidentDate' => now()->subDays(7),
                'SeverityID' => 3, // High
                'ResponsibleUserID' => 2, // Assume Compliance Officer
                'Status' => 'Open',
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'ObligationID' => 2, // Customer KYC Verification
                'Title' => 'Incomplete KYC for New Accounts',
                'Description' => '5 new accounts were onboarded without complete KYC due to system misconfiguration.',
                'IncidentDate' => now()->subDays(14),
                'SeverityID' => 2, // Medium
                'ResponsibleUserID' => 3, // Assume Branch Manager
                'Status' => 'Investigating',
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'ObligationID' => 4, // Data Breach Notification
                'Title' => 'Unreported Data Breach',
                'Description' => 'Customer data was exposed through a misconfigured API. Breach not reported within 72 hours.',
                'IncidentDate' => now()->subDays(30),
                'SeverityID' => 4, // Critical
                'ResponsibleUserID' => 4, // Assume IT Security Lead
                'Status' => 'Escalated',
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
        ]);

        // Corrective actions for the incidents
        DB::table('t_ComplianceIncidentActions')->insert([
            [
                'IncidentID' => 1,
                'RootCause' => 'Server outage due to expired SSL certificate.',
                'CorrectiveAction' => 'Implement certificate monitoring and automated renewal process.',
                'ActionOwnerID' => 5, // IT Operations Lead
                'DueDate' => now()->addDays(10),
                'Status' => 'Pending',
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'IncidentID' => 2,
                'RootCause' => 'System configuration error bypassed KYC checks.',
                'CorrectiveAction' => 'Reconfigure KYC mandatory fields and run data clean-up for impacted accounts.',
                'ActionOwnerID' => 3,
                'DueDate' => now()->addDays(5),
                'Status' => 'Pending',
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
            [
                'IncidentID' => 3,
                'RootCause' => 'API exposed PII data without encryption.',
                'CorrectiveAction' => 'Patch API, enable encryption in transit, and submit breach notification to regulator.',
                'ActionOwnerID' => 4,
                'DueDate' => now()->addDays(2),
                'Status' => 'Pending',
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ],
        ]);
    }
}
