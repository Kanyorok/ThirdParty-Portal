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
        Schema::table('t_FleetVehicleAssignments', function (Blueprint $table) {
            $table->foreign(['AssignedBy'])->references(['Id'])->on('t_Employees')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TripNo'])->references(['Id'])->on('t_TripLogs')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VehicleID'])->references(['Id'])->on('t_FleetVehicles')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VehicleType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetVehicleAssignments', function (Blueprint $table) {
            $table->dropForeign('t_fleetvehicleassignments_assignedby_foreign');
            $table->dropForeign('t_fleetvehicleassignments_createdby_foreign');
            $table->dropForeign('t_fleetvehicleassignments_deletedby_foreign');
            $table->dropForeign('t_fleetvehicleassignments_modifiedby_foreign');
            $table->dropForeign('t_fleetvehicleassignments_tripno_foreign');
            $table->dropForeign('t_fleetvehicleassignments_vehicleid_foreign');
            $table->dropForeign('t_fleetvehicleassignments_vehicletype_foreign');
        });
    }
};
