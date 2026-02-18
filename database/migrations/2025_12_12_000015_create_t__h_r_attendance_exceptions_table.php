<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRAttendanceExceptions', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('AttendanceDailyID')->nullable();
            $table->unsignedBigInteger('EmployeeID');
            $table->date('WorkDate');
            $table->string('Type', 50); // MissingClockOut, ShortShift, Conflict, Other
            $table->string('Status', 30)->default('Open'); // Open, Resolved
            $table->string('Resolution', 255)->nullable();
            $table->unsignedBigInteger('ResolvedBy')->nullable();
            $table->dateTime('ResolvedOn')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRAttendanceExceptions');
    }
};
