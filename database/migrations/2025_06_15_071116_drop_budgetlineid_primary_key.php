<?php

// 2025_06_17_000002_drop_budgetlineid_primary_key.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE t_BudgetMaster DROP CONSTRAINT t_budgetmaster_budgetlineid_primary');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE t_BudgetMaster ADD CONSTRAINT t_budgetmaster_budgetlineid_primary PRIMARY KEY (BudgetLineID)');
    }
};
