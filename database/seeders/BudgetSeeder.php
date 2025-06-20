<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = DB::table('t_Users')->first()->Id ?? 1; // Fallback to 1 if no user exists

        $budgets = [
            [
                'Name' => 'Q2 Budget 2025',
                'FiscalYear' => 2025,
                'From' => '2025-04-04',
                'To' => '2025-07-18',
                'Notes' => 'Budget for Q2 operations and projects.',
                'Status' => 'draft',
            ],
            [
                'Name' => 'Annual Budget 2025-2026',
                'FiscalYear' => 2025,
                'From' => '2025-06-15',
                'To' => '2026-06-15',
                'Notes' => 'Comprehensive budget for 2025-2026 fiscal year.',
                'Status' => 'draft',
            ],
            [
                'Name' => 'Development Budget 2025-2026',
                'FiscalYear' => 2025,
                'From' => '2025-08-02',
                'To' => '2026-08-02',
                'Notes' => 'Budget allocated for development projects.',
                'Status' => 'draft',
            ],
            [
                'Name' => 'Annual Budget 2024-2025',
                'FiscalYear' => 2024,
                'From' => '2024-01-03',
                'To' => '2025-01-03',
                'Notes' => 'Budget for 2024-2025 fiscal year.',
                'Status' => 'draft',
            ],
        ];

        foreach ($budgets as $budget) {
            DB::table('t_Budgets')->insert([
                'Name' => $budget['Name'],
                'FiscalYear' => $budget['FiscalYear'],
                'From' => $budget['From'],
                'To' => $budget['To'],
                'Notes' => $budget['Notes'],
                'Status' => $budget['Status'],
                'CreatedBy' => $userId,
                'CreatedOn' => Carbon::now(),
                'ModifiedBy' => $userId,
                'ModifiedOn' => Carbon::now(),
                'DeletedBy' => null,
                'DeletedOn' => null,
            ]);
        }
    }
}
