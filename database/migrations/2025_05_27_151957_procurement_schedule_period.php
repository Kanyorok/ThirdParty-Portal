<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_SchedulePeriod', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ScheduleId')->constrained('t_SchedulePlan','Id');
            $table->string('SchedulePeriod');
            $table->integer('ScheduleQTY');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
         Schema::dropIfExists('t_Schedule_period');
    }
};
