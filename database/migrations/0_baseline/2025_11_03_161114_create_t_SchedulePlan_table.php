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
        Schema::create('t_SchedulePlan', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ScheduleId')->unique();
            $table->bigInteger('PlanId');
            $table->bigInteger('PlanLineId');
            $table->integer('ScheduleQTY');
            $table->string('Status', 50);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('ScheduleType', 50)->default('Quarterly');

            $table->primary(['Id'], 'pk__t_schedu__3214ec077daa7371');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SchedulePlan');
    }
};
