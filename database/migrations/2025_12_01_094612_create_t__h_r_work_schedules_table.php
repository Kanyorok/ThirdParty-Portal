<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRWorkSchedules', function (Blueprint $table) {
            $table->id('Id');

            $table->unsignedBigInteger('EmployeeID'); // FK t_HREmployees
            $table->date('WorkDate');
            $table->unsignedBigInteger('ShiftID')->nullable(); // FK t_HRShifts
            $table->boolean('IsOffDay')->default(0);

            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->unique(['EmployeeID', 'WorkDate']);

            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
            $table->foreign('ShiftID')->references('Id')->on('t_HRShifts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRWorkSchedules');
    }
};
