<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Auth\User;

class BudgetLineCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $userIds = User::pluck('Id')->toArray();
        if (empty($userIds)) {
            echo "❌ No users found in t_Users. Please seed users first.\n";
            return;
        }

        $categories = [
            ['CAT-001', 'Revenue', 'Income-related budget lines like interest and fee income.', true],
            ['CAT-002', 'Expenses', 'Operational and administrative cost-related lines.', true],
            ['CAT-003', 'Provisions', 'Budget lines for loan losses or risk buffers.', true],
            ['CAT-004', 'Capital Expenditures', 'Items related to long-term investments and branch setup.', true],
            ['CAT-005', 'Compliance & Risk', 'Lines related to regulatory or compliance spending.', false],
        ];

        foreach ($categories as [$code, $name, $desc, $isActive]) {
            DB::table('t_BudgetLineCategories')->insert([
                'CategoryCode' => $code,
                'CategoryName' => $name,
                'Description'  => $desc,
                'IsActive'     => $isActive,
                'CreatedBy'    => $userIds[array_rand($userIds)],
                'CreatedOn'    => $now->copy()->subDays(rand(5, 30)),
                'ModifiedBy'   => $userIds[array_rand($userIds)],
                'ModifiedOn'   => $now->copy()->subDays(rand(1, 4)),
                'DeletedBy'    => null,
                'DeletedOn'    => null,
            ]);
        }
    }
}
