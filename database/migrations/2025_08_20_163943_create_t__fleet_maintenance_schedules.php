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
            $table->id('Id');
            $table->string('ScheduleID')->unique();
            $table->foreignId('VehicleID')->nullable()->constrained('t_FleetVehicles', 'Id');
            $table->foreignId('MaintenanceType')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->date('ScheduledDate')->nullable();
            $table->decimal('ScheduledMileage')->nullable();
            $table->string('Location');
            $table->foreignId('MaintenanceStatus')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->boolean('Status')->nullable();
            $table->string('Notes');
            $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn')->nullable();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn')->nullable();

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
