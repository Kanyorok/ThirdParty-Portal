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
        Schema::table('t_FleetDriverAssignments', function (Blueprint $table) {
            $table->foreign(['AssignedBy'])->references(['Id'])->on('t_Employees')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DriverID'])->references(['Id'])->on('t_FleetDrivers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VehicleID'])->references(['Id'])->on('t_FleetVehicles')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetDriverAssignments', function (Blueprint $table) {
            $table->dropForeign('t_fleetdriverassignments_assignedby_foreign');
            $table->dropForeign('t_fleetdriverassignments_createdby_foreign');
            $table->dropForeign('t_fleetdriverassignments_deletedby_foreign');
            $table->dropForeign('t_fleetdriverassignments_driverid_foreign');
            $table->dropForeign('t_fleetdriverassignments_modifiedby_foreign');
            $table->dropForeign('t_fleetdriverassignments_vehicleid_foreign');
        });
    }
};
