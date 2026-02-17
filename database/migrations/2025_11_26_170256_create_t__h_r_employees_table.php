<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HREmployees', function (Blueprint $table) {
            $table->id('Id');

            $table->string('EmployeeNo', 50)->unique();
            $table->string('FirstName', 100);
            $table->string('LastName', 100);
            $table->string('OtherNames', 150)->nullable();

            $table->string('Email', 150)->nullable();
            $table->string('Phone', 50)->nullable();

            $table->unsignedBigInteger('BranchID');      // FK t_Branches
            $table->unsignedBigInteger('DepartmentID');  // FK t_Departments
            $table->unsignedBigInteger('GradeID')->nullable(); // FK t_HRJobGrades
            $table->unsignedBigInteger('RoleID')->nullable();  // FK t_HRJobRoles
            $table->unsignedBigInteger('SupervisorID')->nullable(); // self FK

            $table->date('EmploymentDate')->nullable();
            $table->string('EmploymentType', 50)->nullable(); // Permanent, Contract, etc.
            $table->string('ContractType', 50)->nullable();

            $table->string('NSSFNo', 50)->nullable();
            $table->string('NHIFNo', 50)->nullable();
            $table->string('KRAPIN', 50)->nullable();

            $table->decimal('BasicSalary', 18, 2)->default(0);
            $table->string('PaymentMode', 50)->default('Bank'); // Bank, Cash, Mobile
            $table->string('BankName', 150)->nullable();
            $table->string('BankBranch', 150)->nullable();
            $table->string('BankAccount', 100)->nullable();

            $table->string('Status', 20)->default('Pending'); 
            // Pending, Active, OnHold, Deactivated, Exited

            $table->boolean('IsActive')->default(1);

            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('BranchID')->references('Id')->on('t_Branches');
            $table->foreign('DepartmentID')->references('Id')->on('t_Departments');
            $table->foreign('GradeID')->references('Id')->on('t_HRJobGrades');
            $table->foreign('RoleID')->references('Id')->on('t_HRJobRoles');
            $table->foreign('SupervisorID')->references('Id')->on('t_HREmployees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HREmployees');
    }
};
