<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('t_HRKPIGoals', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->unsignedBigInteger('PeriodID');
            $table->string('Status', 20)->default('Draft');
            $table->decimal('TotalWeight', 8, 2)->default(0);
            $table->string('Notes', 500)->nullable();
            $table->unsignedBigInteger('SubmittedBy')->nullable();
            $table->dateTime('SubmittedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('RejectedBy')->nullable();
            $table->dateTime('RejectedOn')->nullable();
            $table->string('RejectionReason', 255)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->unique(['EmployeeID', 'PeriodID'], 'ux_kpi_goal_employee_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRKPIGoals');
    }
};
