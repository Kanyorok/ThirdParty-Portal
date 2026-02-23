<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_HRPayrollDeductions', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'IsTaxAllowable')) {
                $table->boolean('IsTaxAllowable')->default(false)->after('ShowInPayslip')
                    ->comment('If true, this deduction reduces taxable income');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_HRPayrollDeductions', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRPayrollDeductions', 'IsTaxAllowable')) {
                $table->dropColumn('IsTaxAllowable');
            }
        });
    }
};
