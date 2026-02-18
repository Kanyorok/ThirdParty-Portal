<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HROvertimeRequests', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->date('WorkDate');
            $table->decimal('HoursRequested', 8, 2)->default(0);
            $table->string('Status', 30)->default('Pending'); // Pending, Approved, Rejected
            $table->string('Reason', 255)->nullable();
            $table->unsignedBigInteger('RequestedBy')->nullable();
            $table->dateTime('RequestedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->string('ApprovalComment', 255)->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HROvertimeRequests');
    }
};
