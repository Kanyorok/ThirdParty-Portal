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
        Schema::table('t_FleetVehicleInspections', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DriverID'])->references(['Id'])->on('t_FleetDrivers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FuelType'])->references(['Id'])->on('t_FuelTypes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['InspectionTypeID'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ParentInspectionID'])->references(['Id'])->on('t_FleetVehicleInspections')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VehicleID'])->references(['Id'])->on('t_FleetVehicles')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetVehicleInspections', function (Blueprint $table) {
            $table->dropForeign('t_fleetvehicleinspections_createdby_foreign');
            $table->dropForeign('t_fleetvehicleinspections_deletedby_foreign');
            $table->dropForeign('t_fleetvehicleinspections_driverid_foreign');
            $table->dropForeign('t_fleetvehicleinspections_fueltype_foreign');
            $table->dropForeign('t_fleetvehicleinspections_inspectiontypeid_foreign');
            $table->dropForeign('t_fleetvehicleinspections_modifiedby_foreign');
            $table->dropForeign('t_fleetvehicleinspections_parentinspectionid_foreign');
            $table->dropForeign('t_fleetvehicleinspections_vehicleid_foreign');
        });
    }
};
