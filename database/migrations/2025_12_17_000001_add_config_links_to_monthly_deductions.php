<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRMonthlyDeductions', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRMonthlyDeductions', 'DeductionID')) {
                $table->unsignedBigInteger('DeductionID')->nullable()->after('EmployeeID');
            }
            if (!Schema::hasColumn('t_HRMonthlyDeductions', 'IsRecurring')) {
                $table->boolean('IsRecurring')->default(false)->after('Amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRMonthlyDeductions', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRMonthlyDeductions', 'DeductionID')) {
                $table->dropColumn('DeductionID');
            }
            if (Schema::hasColumn('t_HRMonthlyDeductions', 'IsRecurring')) {
                $table->dropColumn('IsRecurring');
            }
        });
    }
};
