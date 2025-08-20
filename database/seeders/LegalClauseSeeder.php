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

        $clauses = [
            [
                'Title' => 'Confidentiality Clause',
                'ClauseType' => 'Confidentiality',
                'Content' => 'All parties agree to maintain confidentiality of proprietary information.',
                'IsStandard' => 'Yes',
                'Version' => '1.0',
            ],
            [
                'Title' => 'Termination Clause',
                'ClauseType' => 'Termination',
                'Content' => 'Either party may terminate the agreement with 30 days written notice.',
                'IsStandard' => 'No',
                'Version' => '1.0',
            ]
        ];

        foreach ($clauses as $clause) {
            $exists = DB::table('t_LegalClauses')
                ->where('Title', $clause['Title'])
                ->exists();

            if (!$exists) {
                DB::table('t_LegalClauses')->insert([
                    'Title' => $clause['Title'],
                    'ClauseType' => $clause['ClauseType'],
                    'Content' => $clause['Content'],
                    'IsStandard' => $clause['IsStandard'],
                    'Version' => $clause['Version'],
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
