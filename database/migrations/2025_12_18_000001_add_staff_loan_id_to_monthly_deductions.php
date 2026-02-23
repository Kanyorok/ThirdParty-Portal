<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRMonthlyDeductions', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRMonthlyDeductions', 'StaffLoanID')) {
                $table->unsignedBigInteger('StaffLoanID')->nullable()->after('DeductionID');
                $table->index(['StaffLoanID'], 'IDX_HRMonthlyDeductions_StaffLoanID');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRMonthlyDeductions', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRMonthlyDeductions', 'StaffLoanID')) {
                $table->dropIndex('IDX_HRMonthlyDeductions_StaffLoanID');
                $table->dropColumn('StaffLoanID');
            }
        });
    }
};

