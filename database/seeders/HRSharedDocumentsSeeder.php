<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HRSharedDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $actor = 1;

        $categories = [
            ['Code' => 'POLICY', 'Name' => 'HR Policies', 'Description' => 'Policies and policy updates.'],
            ['Code' => 'HANDBOOK', 'Name' => 'Employee Handbooks', 'Description' => 'Employee handbook documents.'],
            ['Code' => 'CIRC', 'Name' => 'Circulars & Notices', 'Description' => 'HR circulars and staff notices.'],
            ['Code' => 'SOP', 'Name' => 'SOPs & Guidelines', 'Description' => 'Standard operating procedures and guidelines.'],
            ['Code' => 'COMP', 'Name' => 'Compliance', 'Description' => 'Compliance and regulatory documents.'],
            ['Code' => 'TEMPL', 'Name' => 'Templates', 'Description' => 'Forms, letters, and contracts.'],
            ['Code' => 'LEARN', 'Name' => 'Learning Materials', 'Description' => 'Training materials and references.'],
        ];

        foreach ($categories as $category) {
            DB::table('t_HRSharedDocumentCategories')->updateOrInsert(
                ['Code' => $category['Code']],
                array_merge($category, [
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                ])
            );
        }

        $handbookId = DB::table('t_HRSharedDocumentCategories')->where('Code', 'HANDBOOK')->value('Id');
        if ($handbookId) {
            DB::table('t_HRSharedDocuments')->updateOrInsert(
                ['Title' => 'Employee Handbook'],
                [
                    'CategoryID' => $handbookId,
                    'Description' => 'Company handbook for all staff.',
                    'Version' => '1.0',
                    'EffectiveDate' => $now->toDateString(),
                    'AccessLevel' => 'Public',
                    'IsDownloadable' => 1,
                    'IsMandatory' => 1,
                    'Status' => 'Published',
                    'IsActive' => 1,
                    'CreatedBy' => $actor,
                    'CreatedOn' => $now,
                ]
            );
        }
    }
}
