<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRPayrollEmployerContributions', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('PayrollRunID');
            $table->unsignedBigInteger('EmployeeID');
            $table->unsignedBigInteger('DeductionID');
            $table->integer('Month');
            $table->integer('Year');
            $table->decimal('BaseAmount', 18, 2)->default(0);
            $table->decimal('Rate', 10, 4)->nullable();
            $table->string('CalcMethod', 50)->nullable();
            $table->decimal('Amount', 18, 2)->default(0);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->unique(['PayrollRunID', 'EmployeeID', 'DeductionID'], 'ux_hr_employer_contrib_run');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRPayrollEmployerContributions');
    }
};
