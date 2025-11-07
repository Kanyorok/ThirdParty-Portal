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
        Schema::create('t_ScheduleUsers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('UserID');
            $table->bigInteger('ScheduleId');
            $table->char('ScheduleUserStatus', 2);
            $table->dateTime('DecidedOn')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->dateTime('ReminderOn')->nullable();

            $table->primary(['id'], 'pk__t_schedu__3213e83f50f77e20');
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
