<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LegalIntellectualPropertiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $records = [
            [
                'Title' => 'Toyota Logo Design',
                'IPType' => 'Trademark',
                'RegistrationNumber' => 'TM-2024-001',
                'RegistrationDate' => '2024-01-15',
                'ExpiryDate' => '2034-01-15',
                'Status' => 'Active',
                'Owner' => 'Toyota Motor Corporation',
                // 'Jurisdiction' => 'Kenya',   
                'DMSDocID' => 1001,
                'Remarks' => 'Trademark for corporate logo and branding.',
                'IsDisputed' => false,
                'DisputeReason' => null,
            ],
            [
                'Title' => 'Solar Water Pump Design',
                'IPType' => 'Patent',
                'RegistrationNumber' => 'PT-10234',
                'RegistrationDate' => '2018-06-20',
                'ExpiryDate' => '2028-06-20',
                'Status' => 'Active',
                'Owner' => 'Mary Wanjiru',
                // 'Jurisdiction' => 'Kenya',
                'DMSDocID' => 1002,
                'Remarks' => 'Patent for energy-efficient solar water pump.',
                'IsDisputed' => false,
                'DisputeReason' => null,
            ],
            [
                'Title' => 'African Wildlife Photography Collection',
                'IPType' => 'Copyright',
                'RegistrationNumber' => 'CR-56789',
                'RegistrationDate' => '2020-03-10',
                'ExpiryDate' => '2028-06-20',
                'Status' => 'Active',
                'Owner' => 'Samuel Otieno',
                // 'Jurisdiction' => 'International',
                'DMSDocID' => 1003,
                'Remarks' => 'Copyright for original wildlife photographs.',
                'IsDisputed' => false,
                'DisputeReason' => null,
            ],
            [
                'Title' => 'E-Payments Processing Algorithm',
                'IPType' => 'Patent',
                'RegistrationNumber' => 'PT-54321',
                'RegistrationDate' => '2015-11-05',
                'ExpiryDate' => '2025-11-05',
                'Status' => 'Active',
                'Owner' => 'FinTech Africa Ltd',
                // 'Jurisdiction' => 'Kenya',
                'DMSDocID' => 1004,
                'Remarks' => 'Patent pending renewal for payment system technology.',
                'IsDisputed' => false,
                'DisputeReason' => null,
            ],
        ];

        foreach ($records as $record) {
            $exists = DB::table('t_LegalIntellectualProperties')
                ->where('Title', $record['Title'])
                ->exists();

            if (!$exists) {
                DB::table('t_LegalIntellectualProperties')->insert([
                    'Title' => $record['Title'],
                    'IPType' => $record['IPType'],
                    'RegistrationNumber' => $record['RegistrationNumber'],
                    'RegistrationDate' => $record['RegistrationDate'],
                    'ExpiryDate' => $record['ExpiryDate'],
                    'Status' => $record['Status'],
                    'Owner' => $record['Owner'],
                    // 'Jurisdiction' => $record['Jurisdiction'],
                    'DMSDocID' => $record['DMSDocID'],
                    'Remarks' => $record['Remarks'],
                    'IsDisputed' => $record['IsDisputed'],
                    'DisputeReason' => $record['DisputeReason'],
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                    'DeletedOn' => null
                ]);
            }
        }
    }
}
