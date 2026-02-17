<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_HRPayrollAllowances', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRPayrollAllowances', 'IsMandatory')) {
                $table->boolean('IsMandatory')->default(false)->after('IsTaxable');
            }
        });

        if (!Schema::hasTable('t_HRPayrollAllowanceGrades')) {
            Schema::create('t_HRPayrollAllowanceGrades', function (Blueprint $table) {
                $table->id('Id');
                $table->unsignedBigInteger('AllowanceID');
                $table->unsignedBigInteger('GradeID');
                $table->unique(['AllowanceID','GradeID'], 'uq_allowance_grade');
            });
        }

        Schema::table('t_HRMonthlyAllowances', function (Blueprint $table) {
            if (!Schema::hasColumn('t_HRMonthlyAllowances', 'AllowanceID')) {
                $table->unsignedBigInteger('AllowanceID')->nullable()->after('EmployeeID');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_HRPayrollAllowances', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRPayrollAllowances', 'IsMandatory')) {
                $table->dropColumn('IsMandatory');
            }
        });
        Schema::dropIfExists('t_HRPayrollAllowanceGrades');
        Schema::table('t_HRMonthlyAllowances', function (Blueprint $table) {
            if (Schema::hasColumn('t_HRMonthlyAllowances', 'AllowanceID')) {
                $table->dropColumn('AllowanceID');
            }
        });
    }
};
