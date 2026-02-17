<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRMonthlyDeductions', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRMonthlyDeductions', 'IsAutoCalculated')) {
                $table->boolean('IsAutoCalculated')->default(true)->after('IsRecurring');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRMonthlyDeductions', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRMonthlyDeductions', 'IsAutoCalculated')) {
                $table->dropColumn('IsAutoCalculated');
            }
        });
    }
};
