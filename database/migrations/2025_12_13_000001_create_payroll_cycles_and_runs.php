<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRPayrollCycles', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedSmallInteger('Year');
            $table->unsignedTinyInteger('Month'); // 1 - 12
            $table->string('Status', 20)->default('Open'); // Open, Closed, Reopened
            $table->dateTime('OpenedOn')->nullable();
            $table->unsignedBigInteger('OpenedBy')->nullable();
            $table->dateTime('ClosedOn')->nullable();
            $table->unsignedBigInteger('ClosedBy')->nullable();
            $table->dateTime('ReopenedOn')->nullable();
            $table->unsignedBigInteger('ReopenedBy')->nullable();
            $table->string('Notes', 500)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->unique(['Year','Month']);
        });

        Schema::create('t_HRPayrollRuns', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('PayrollCycleID');
            $table->string('Status', 20)->default('Draft'); // Draft, Generated, Approved, Posted
            $table->dateTime('GeneratedOn')->nullable();
            $table->unsignedBigInteger('GeneratedBy')->nullable();
            $table->string('Notes', 500)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreign('PayrollCycleID')->references('Id')->on('t_HRPayrollCycles')->cascadeOnDelete();
        });

        Schema::create('t_HRPayrollRunLines', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('PayrollRunID');
            $table->unsignedBigInteger('EmployeeID');
            $table->decimal('BasicSalary', 18, 2)->default(0);
            $table->decimal('TotalAllowances', 18, 2)->default(0);
            $table->decimal('TotalDeductions', 18, 2)->default(0);
            $table->decimal('StatutoryDeductions', 18, 2)->default(0);
            $table->decimal('LoanDeductions', 18, 2)->default(0);
            $table->decimal('Overtime', 18, 2)->default(0);
            $table->decimal('AttendanceAdjustments', 18, 2)->default(0);
            $table->decimal('LeaveAdjustments', 18, 2)->default(0);
            $table->decimal('GrossPay', 18, 2)->default(0);
            $table->decimal('NetPay', 18, 2)->default(0);
            $table->string('Currency', 10)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreign('PayrollRunID')->references('Id')->on('t_HRPayrollRuns')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRPayrollRunLines');
        Schema::dropIfExists('t_HRPayrollRuns');
        Schema::dropIfExists('t_HRPayrollCycles');
    }
};
