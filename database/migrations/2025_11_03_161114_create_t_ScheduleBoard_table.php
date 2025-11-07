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
        Schema::create('t_ScheduleBoard', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('BoardMemberId');
            $table->bigInteger('ScheduleId');
            $table->char('ScheduleStatus', 2);
            $table->dateTime('DecidedOn')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            $table->primary(['id'], 'pk__t_schedu__3213e83f497fdc2a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ScheduleBoard');
    }
};
