<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LegalCaseEvidenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $evidenceItems = [
            [
                'LegalCaseID' => 1,
                'EvidenceTitle' => 'Signed Agreement',
                'Description' => 'Official signed agreement relevant to the case.',
                'DMSDocumentID' => null,
                'ExternalLink' => null,
                'IsActive' => 'Active',
                'UploadedBy' => 1,
                'UploadedOn' => $now,
            ],
            [
                'LegalCaseID' => 2,
                'EvidenceTitle' => 'Witness Statement',
                'Description' => 'PDF file of the primary witness testimony.',
                'DMSDocumentID' => null,
                'ExternalLink' => null,
                'IsActive' => 'Active',
                'UploadedBy' => 2,
                'UploadedOn' => $now,
            ],
        ];

        foreach ($evidenceItems as $item) {
            $exists = DB::table('t_LegalCaseEvidence')
                ->where('LegalCaseID', $item['LegalCaseID'])
                ->where('EvidenceTitle', $item['EvidenceTitle'])
                ->exists();

            if (! $exists) {
                DB::table('t_LegalCaseEvidence')->insert([
                    'LegalCaseID' => $item['LegalCaseID'],
                    'EvidenceTitle' => $item['EvidenceTitle'],
                    'Description' => $item['Description'],
                    'DMSDocumentID' => $item['DMSDocumentID'],
                    'ExternalLink' => $item['ExternalLink'],
                    'IsActive' => $item['IsActive'],
                    'UploadedBy' => $item['UploadedBy'],
                    'UploadedOn' => $item['UploadedOn'],
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                    'DeletedOn' => null,
                ]);
            }
        }
    }
}
