<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRSalaryHistory', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->date('EffectiveDate');
            $table->decimal('BasicSalary', 18, 2);
            $table->string('Reason', 250)->nullable();
            $table->string('Status', 20)->default('Active'); // Active, Superseded
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
        });

        Schema::create('t_HRSalaryAdjustments', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->string('Type', 20); // Increment, Decrement
            $table->decimal('Amount', 18, 2);
            $table->date('EffectiveDate');
            $table->string('Reason', 250)->nullable();
            $table->string('Status', 20)->default('Pending'); // Pending, Approved, Rejected
            $table->unsignedBigInteger('RequestedBy')->nullable();
            $table->dateTime('RequestedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRSalaryAdjustments');
        Schema::dropIfExists('t_HRSalaryHistory');
    }
};
