<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HREmployeeWorkingDays', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('EmployeeID');
            $table->unsignedTinyInteger('DayOfWeek'); // 0=Sunday ... 6=Saturday
            $table->boolean('IsWorking')->default(false);
            $table->decimal('DayFraction', 3, 2)->default(1.00);
            $table->time('StartTime')->nullable();
            $table->time('EndTime')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->unique(['EmployeeID', 'DayOfWeek'], 'ux_employee_working_day');
            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HREmployeeWorkingDays');
    }
};
