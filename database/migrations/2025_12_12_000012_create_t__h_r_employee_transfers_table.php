<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HREmployeeTransfers', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->unsignedBigInteger('FromBranchID')->nullable();
            $table->unsignedBigInteger('ToBranchID')->nullable();
            $table->unsignedBigInteger('FromDepartmentID')->nullable();
            $table->unsignedBigInteger('ToDepartmentID')->nullable();
            $table->unsignedBigInteger('FromRoleID')->nullable();
            $table->unsignedBigInteger('ToRoleID')->nullable();
            $table->date('EffectiveDate')->nullable();
            $table->string('Reason', 255)->nullable();
            $table->string('Status', 30)->default('Pending');
            $table->unsignedBigInteger('RequestedBy')->nullable();
            $table->dateTime('RequestedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->string('ApprovalComment', 255)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HREmployeeTransfers');
    }
};
