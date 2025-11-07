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
        Schema::create('t_FleetMaintenanceSchedules', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ScheduleID')->unique();
            $table->bigInteger('VehicleID')->nullable();
            $table->bigInteger('MaintenanceType')->nullable();
            $table->date('ScheduledDate')->nullable();
            $table->decimal('ScheduledMileage')->nullable();
            $table->string('Location');
            $table->bigInteger('MaintenanceStatus')->nullable();
            $table->boolean('Status')->nullable();
            $table->string('Notes')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_fleetm__3214ec07312d935c');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FleetMaintenanceSchedules');
    }
};
