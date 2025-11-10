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
        Schema::create('t_ScheduleUsers', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('UserID')->constrained('t_Users', 'Id');
            $table->foreignId('ScheduleId')->constrained('t_Schedule', 'ScheduleID');
            $table->char('ScheduleUserStatus', 2);
            $table->dateTime('DecidedOn')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ScheduleUsers');
    }
};
