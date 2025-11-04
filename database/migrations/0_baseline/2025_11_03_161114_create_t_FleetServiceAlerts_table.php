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
        Schema::create('t_FleetServiceAlerts', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('AlertID')->unique();
            $table->bigInteger('VehicleID')->nullable();
            $table->bigInteger('ScheduleID')->nullable();
            $table->bigInteger('AlertType')->nullable();
            $table->date('TriggerDate')->nullable();
            $table->decimal('TriggerMileage')->nullable();
            $table->string('Description')->nullable();
            $table->boolean('IsAcknowledged')->nullable();
            $table->date('AcknowledgedOn')->nullable();
            $table->bigInteger('AcknowledgedBy')->nullable();
            $table->bigInteger('MaintenanceStatus')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_fleets__3214ec07ff41000f');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FleetServiceAlerts');
    }
};
