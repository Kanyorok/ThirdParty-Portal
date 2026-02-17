<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRPayrollDeductionRules', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollDeductionRules', 'FormulaText')) {
                $table->text('FormulaText')->nullable()->after('Description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRPayrollDeductionRules', function (Blueprint $table) {
            $table->dropColumn(['FormulaText']);
        });
    }
};
