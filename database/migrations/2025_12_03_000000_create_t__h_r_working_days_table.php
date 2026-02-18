<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRWorkingDays', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedTinyInteger('DayOfWeek')->unique(); // 0=Sunday ... 6=Saturday
            $table->boolean('IsWorking')->default(false);
            $table->time('StartTime')->nullable();
            $table->time('EndTime')->nullable();
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRWorkingDays');
    }
};
