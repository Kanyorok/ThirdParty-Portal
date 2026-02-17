<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Any working day that was saved with a zero fraction should be a half day.
        DB::table('t_HRWorkingDays')
            ->where('IsWorking', 1)
            ->where(function ($q) {
                $q->whereNull('DayFraction')
                  ->orWhere('DayFraction', 0);
            })
            ->update(['DayFraction' => 0.50]);
    }

    public function down(): void
    {
        // No rollback; leaving data as-is to avoid data loss.
    }
};
