<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LegalClauseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $rows = [
            [
                'Title' => 'Confidentiality Clause',
                'ClauseType' => 'Confidentiality',
                'Content' => 'All parties agree to maintain confidentiality of proprietary information.',
                'IsStandard' => 1,                  // boolean, not "Yes"
                'Version' => 'v1.0',             // keep versioning consistent
                'Status' => 'ACTIVE',           // matches migration default
                'EffectiveFrom' => $now->toDateString(),
                'EffectiveTo' => null,
                'Jurisdiction' => 'Kenya',
                'ClauseDMSDocID' => null,
                'Tags' => json_encode(['confidentiality', 'nda']),
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => null,
                'ModifiedOn' => null,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'Title' => 'Termination Clause',
                'ClauseType' => 'Termination',
                'Content' => 'Either party may terminate the agreement with 30 days written notice.',
                'IsStandard' => 0,
                'Version' => 'v1.0',
                'Status' => 'ACTIVE',
                'EffectiveFrom' => $now->toDateString(),
                'EffectiveTo' => null,
                'Jurisdiction' => 'Kenya',
                'ClauseDMSDocID' => null,
                'Tags' => json_encode(['termination']),
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => null,
                'ModifiedOn' => null,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
        ];

        foreach ($rows as $row) {
            $exists = DB::table('t_LegalClauses')
                ->where('Title', $row['Title'])
                ->where('Version', $row['Version'])
                ->exists();

            if (!$exists) {
                DB::table('t_LegalClauses')->insert($row);
            }
        }
    }
}
