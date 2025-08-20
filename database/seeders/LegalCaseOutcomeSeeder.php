<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LegalCaseOutcomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $outcomes = [
            [
                'LegalCaseID'   => 1,
                'Outcome'       => 'Case Dismissed',
                'JudgmentDate'  => Carbon::parse('2025-01-15'),
                'JudgeName'     => 'Justice Kamau',
                'CourtDecision' => 'The court found insufficient evidence and dismissed the case.',
                'PenaltyAmount' => null,
                'Remarks'       => 'Plaintiff may appeal within 30 days.',
            ],
            [
                'LegalCaseID'   => 2,
                'Outcome'       => 'Defendant Fined',
                'JudgmentDate'  => Carbon::parse('2025-02-01'),
                'JudgeName'     => 'Justice Achieng',
                'CourtDecision' => 'The court ordered the defendant to pay damages.',
                'PenaltyAmount' => 50000.00,
                'Remarks'       => 'Payment to be made within 60 days.',
            ],
            [
                'LegalCaseID'   => 1,
                'Outcome'       => 'Settlement Reached',
                'JudgmentDate'  => Carbon::parse('2025-03-05'),
                'JudgeName'     => 'Justice Mwangi',
                'CourtDecision' => 'Both parties agreed to settle the matter out of court.',
                'PenaltyAmount' => null,
                'Remarks'       => 'Case closed by mutual agreement.',
            ],
        ];

        foreach ($outcomes as $item) {
            $exists = DB::table('t_LegalCaseOutcomes')
                ->where('LegalCaseID', $item['LegalCaseID'])
                ->where('Outcome', $item['Outcome'])
                ->exists();

            if (!$exists) {
                DB::table('t_LegalCaseOutcomes')->insert([
                    'LegalCaseID'   => $item['LegalCaseID'],
                    'Outcome'       => $item['Outcome'],
                    'JudgmentDate'  => $item['JudgmentDate'],
                    'JudgeName'     => $item['JudgeName'],
                    'CourtDecision' => $item['CourtDecision'],
                    'PenaltyAmount' => $item['PenaltyAmount'],
                    'Remarks'       => $item['Remarks'],
                    'CreatedBy'     => 1,
                    'CreatedOn'     => $now,
                    'ModifiedBy'    => 1,
                    'ModifiedOn'    => $now,
                    'DeletedBy'     => null,
                    'DeletedOn'     => null,
                ]);
            }
        }
    }
}
