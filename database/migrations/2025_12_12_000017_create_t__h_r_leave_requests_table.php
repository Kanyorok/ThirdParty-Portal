<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRLeaveRequests', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->unsignedBigInteger('LeaveTypeID');
            $table->date('StartDate');
            $table->date('EndDate');
            $table->decimal('TotalDays', 6, 2)->default(0);
            $table->string('Reason', 500)->nullable();
            $table->string('Status', 30)->default('Pending'); // Pending, Approved, Rejected, Cancelled
            $table->unsignedBigInteger('RequestedBy')->nullable();
            $table->dateTime('RequestedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->string('ApprovalComment', 500)->nullable();
            $table->unsignedBigInteger('CancelledBy')->nullable();
            $table->dateTime('CancelledOn')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRLeaveRequests');
    }
};
