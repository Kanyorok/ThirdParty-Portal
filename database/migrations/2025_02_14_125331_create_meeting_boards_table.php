<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_MeetingBoard', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('BoardMemberId')->constrained('t_BoardMembers', 'Id');
            $table->foreignId('MeetingId')->constrained('t_Meetings', 'MeetingID');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
        });

        Schema::create('t_ScheduleBoard', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('BoardMemberId')->constrained('t_BoardMembers', 'Id');
            $table->foreignId('ScheduleId')->constrained('t_Schedule', 'ScheduleID');
            $table->char('ScheduleStatus', 2);
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
        Schema::dropIfExists('t_ScheduleBoard');
        Schema::dropIfExists('t_MeetingBoard');
    }
};
