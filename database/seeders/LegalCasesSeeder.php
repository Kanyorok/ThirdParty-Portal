<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LegalCasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $cases = [
            [
                'CaseTitle'        => 'John Doe vs ABC Corporation',
                'CaseNumber'       => 'CIV-2025-001',
                'CourtName'        => 'High Court',
                'FilingDate'       => '2025-01-15',
                'OpposingParty'    => 'ABC Corporation',
                'CaseType'         => 'Civil',
                'Status'           => 'Ongoing',
                'Summary'          => 'A civil dispute regarding breach of contract.',
                'AssignedCounselID'=> 1,
                'CaseDMSDocID'         => 101,
                'IsActive'         => 1,
                'CreatedBy'        => 1,
                'CreatedOn'        => $now,
                'ModifiedBy'       => 1,
                'ModifiedOn'       => $now,
                'DeletedBy'        => null,
                'DeletedOn'        => null
            ],
            [
                'CaseTitle'        => 'Jane Smith vs XYZ Ltd',
                'CaseNumber'       => 'CRIM-2025-002',
                'CourtName'        => 'Magistrates Court',
                'FilingDate'       => '2025-02-05',
                'OpposingParty'    => 'XYZ Ltd',
                'CaseType'         => 'Criminal',
                'Status'           => 'Closed',
                'Summary'          => 'A criminal case involving corporate fraud.',
                'AssignedCounselID'=> 2,
                'CaseDMSDocID'         => 102,
                'IsActive'         => 1,
                'CreatedBy'        => 1,
                'CreatedOn'        => $now,
                'ModifiedBy'       => 1,
                'ModifiedOn'       => $now,
                'DeletedBy'        => null,
                'DeletedOn'        => null
            ]
        ];

        foreach ($cases as $case) {
            $exists = DB::table('t_LegalCases')->where('CaseNumber', $case['CaseNumber'])->exists();

            if (!$exists) {
                DB::table('t_LegalCases')->insert($case);
            }
        }
    }
}
