<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRPayrollDeductionRules', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollDeductionRules', 'AffectsTaxableIncome')) {
                $table->boolean('AffectsTaxableIncome')->default(false)->after('HasRelief');
            }
            if (!Schema::hasColumn('t_HRPayrollDeductionRules', 'IsTaxRelief')) {
                $table->boolean('IsTaxRelief')->default(false)->after('AffectsTaxableIncome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRPayrollDeductionRules', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRPayrollDeductionRules', 'AffectsTaxableIncome')) {
                $table->dropColumn('AffectsTaxableIncome');
            }
            if (Schema::hasColumn('t_HRPayrollDeductionRules', 'IsTaxRelief')) {
                $table->dropColumn('IsTaxRelief');
            }
        });
    }
};
