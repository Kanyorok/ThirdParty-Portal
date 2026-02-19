<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRPayrollDeductions', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'EmployerContributionEnabled')) {
                $table->boolean('EmployerContributionEnabled')->default(0);
            }
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'EmployerCalcMethod')) {
                $table->string('EmployerCalcMethod', 50)->nullable();
            }
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'EmployerRate')) {
                $table->decimal('EmployerRate', 10, 4)->nullable();
            }
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'EmployerAmount')) {
                $table->decimal('EmployerAmount', 18, 2)->nullable();
            }
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'EmployerDebitGLAccountID')) {
                $table->unsignedBigInteger('EmployerDebitGLAccountID')->nullable();
            }
            if (!Schema::hasColumn('t_HRPayrollDeductions', 'EmployerCreditGLAccountID')) {
                $table->unsignedBigInteger('EmployerCreditGLAccountID')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRPayrollDeductions', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRPayrollDeductions', 'EmployerCreditGLAccountID')) {
                $table->dropColumn('EmployerCreditGLAccountID');
            }
            if (Schema::hasColumn('t_HRPayrollDeductions', 'EmployerDebitGLAccountID')) {
                $table->dropColumn('EmployerDebitGLAccountID');
            }
            if (Schema::hasColumn('t_HRPayrollDeductions', 'EmployerAmount')) {
                $table->dropColumn('EmployerAmount');
            }
            if (Schema::hasColumn('t_HRPayrollDeductions', 'EmployerRate')) {
                $table->dropColumn('EmployerRate');
            }
            if (Schema::hasColumn('t_HRPayrollDeductions', 'EmployerCalcMethod')) {
                $table->dropColumn('EmployerCalcMethod');
            }
            if (Schema::hasColumn('t_HRPayrollDeductions', 'EmployerContributionEnabled')) {
                $table->dropColumn('EmployerContributionEnabled');
            }
        });
    }
};
