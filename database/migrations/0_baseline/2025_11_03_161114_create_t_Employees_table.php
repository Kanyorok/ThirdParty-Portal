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
        Schema::create('t_Employees', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('EmployeeID')->unique();
            $table->string('FirstName');
            $table->string('LastName');
            $table->string('MiddleName')->nullable();
            $table->string('Email')->unique();
            $table->string('Phone')->nullable();
            $table->string('Address')->nullable();
            $table->date('DateOfBirth')->nullable();
            $table->date('JoinDate');
            $table->bigInteger('DepartmentId');
            $table->bigInteger('BranchId');
            $table->string('JobTitle');
            $table->bigInteger('ImageId')->nullable();
            $table->string('Gender', 1)->default('o');
            $table->string('MaritalStatus', 2)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_employ__3214ec07da277192');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Employees');
    }
};
