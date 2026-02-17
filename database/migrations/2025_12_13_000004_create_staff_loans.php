<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRStaffLoans', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Name', 150)->default('Staff Loan');
            $table->decimal('Principal', 18, 2);
            $table->decimal('InterestRate', 9, 4)->default(0); // percent per annum
            $table->unsignedSmallInteger('TenureMonths')->default(0);
            $table->decimal('InstallmentAmount', 18, 2)->default(0);
            $table->decimal('Balance', 18, 2)->default(0);
            $table->date('StartDate')->nullable();
            $table->date('EndDate')->nullable();
            $table->string('Status', 20)->default('Pending'); // Pending, Active, Closed, Rejected
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
        });

        Schema::create('t_HRStaffLoanSchedules', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('StaffLoanID');
            $table->unsignedSmallInteger('InstallmentNo');
            $table->date('DueDate');
            $table->decimal('PrincipalComponent', 18, 2)->default(0);
            $table->decimal('InterestComponent', 18, 2)->default(0);
            $table->decimal('TotalDue', 18, 2)->default(0);
            $table->string('Status', 20)->default('Pending'); // Pending, Paid
            $table->unsignedBigInteger('PaidBy')->nullable();
            $table->dateTime('PaidOn')->nullable();
            $table->foreign('StaffLoanID')->references('Id')->on('t_HRStaffLoans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRStaffLoanSchedules');
        Schema::dropIfExists('t_HRStaffLoans');
    }
};
