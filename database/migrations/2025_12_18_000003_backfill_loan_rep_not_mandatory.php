<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('t_HRPayrollDeductions')
            ->where('Code', 'LOAN-REP')
            ->update([
                'IsMandatory' => 0,
            ]);
    }

    public function down(): void
    {
        // No-op: do not revert, since LOAN-REP should not be mandatory.
    }
};

