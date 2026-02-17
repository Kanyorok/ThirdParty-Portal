<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRAttendanceDaily', function (Blueprint $table) {
            $table->id('Id');

            $table->unsignedBigInteger('EmployeeID');
            $table->date('WorkDate');
            $table->unsignedBigInteger('ShiftID')->nullable(); // expected shift

            $table->dateTime('FirstInTime')->nullable();
            $table->dateTime('LastOutTime')->nullable();

            $table->decimal('TotalHours', 8, 2)->nullable();
            $table->decimal('OvertimeHours', 8, 2)->nullable();

            $table->string('Status', 20)->default('Absent');
            // Present, Absent, OnLeave, Late, EarlyExit, HalfDay, OffDay

            $table->integer('LateMinutes')->nullable();
            $table->integer('EarlyExitMinutes')->nullable();

            $table->boolean('IsManualAdjusted')->default(0);
            $table->string('AdjustmentReason', 255)->nullable();

            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->unique(['EmployeeID', 'WorkDate']);

            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
            $table->foreign('ShiftID')->references('Id')->on('t_HRShifts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRAttendanceDaily');
    }
};
