<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRLeaveTypeGrades', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('LeaveTypeID');
            $table->unsignedBigInteger('GradeID');
            $table->foreign('LeaveTypeID')->references('Id')->on('t_HRLeaveTypes')->onDelete('cascade');
            $table->foreign('GradeID')->references('Id')->on('t_HRJobGrades')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRLeaveTypeGrades');
    }
};
