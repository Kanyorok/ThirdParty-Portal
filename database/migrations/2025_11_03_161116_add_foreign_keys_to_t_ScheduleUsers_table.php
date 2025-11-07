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
        Schema::table('t_ScheduleUsers', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ScheduleId'])->references(['ScheduleID'])->on('t_Schedule')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['UserID'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ScheduleUsers', function (Blueprint $table) {
            $table->dropForeign('t_scheduleusers_createdby_foreign');
            $table->dropForeign('t_scheduleusers_modifiedby_foreign');
            $table->dropForeign('t_scheduleusers_scheduleid_foreign');
            $table->dropForeign('t_scheduleusers_userid_foreign');
        });
    }
};
