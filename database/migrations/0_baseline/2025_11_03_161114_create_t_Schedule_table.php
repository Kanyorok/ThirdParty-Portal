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
        Schema::create('t_Schedule', function (Blueprint $table) {
            $table->bigIncrements('ScheduleID');
            $table->string('Title', 200);
            $table->text('Notes');
            $table->string('ScheduledType')->nullable();
            $table->bigInteger('ScheduledTypeID')->nullable();
            $table->char('ScheduleStatusID', 2)->default('sc');
            $table->dateTime('StartOn');
            $table->dateTime('EndOn');
            $table->string('Type');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('Source', 100)->nullable();
            $table->string('SourceID', 100)->nullable();

            $table->primary(['ScheduleID'], 'pk__t_schedu__9c8a5b6907ef45b8');
            $table->index(['ScheduledType', 'ScheduledTypeID']);
            $table->index(['Source', 'SourceID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Schedule');
    }
};
