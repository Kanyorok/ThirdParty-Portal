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
        Schema::create('t_ScheduleClients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ClientID', 70)->index();
            $table->bigInteger('ScheduleId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->dateTime('ReminderOn')->nullable();

            $table->primary(['id'], 'pk__t_schedu__3213e83fb4de8d28');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ScheduleClients');
    }
};
