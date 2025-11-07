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
        Schema::table('t_FleetServiceAlerts', function (Blueprint $table) {
            $table->foreign(['AcknowledgedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['AlertType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['MaintenanceStatus'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ScheduleID'])->references(['Id'])->on('t_FleetMaintenanceSchedules')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VehicleID'])->references(['Id'])->on('t_FleetVehicles')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetServiceAlerts', function (Blueprint $table) {
            $table->dropForeign('t_fleetservicealerts_acknowledgedby_foreign');
            $table->dropForeign('t_fleetservicealerts_alerttype_foreign');
            $table->dropForeign('t_fleetservicealerts_createdby_foreign');
            $table->dropForeign('t_fleetservicealerts_deletedby_foreign');
            $table->dropForeign('t_fleetservicealerts_maintenancestatus_foreign');
            $table->dropForeign('t_fleetservicealerts_modifiedby_foreign');
            $table->dropForeign('t_fleetservicealerts_scheduleid_foreign');
            $table->dropForeign('t_fleetservicealerts_vehicleid_foreign');
        });
    }
};
