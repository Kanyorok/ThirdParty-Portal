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
        Schema::table('t_FleetRepairLogs', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RepairType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ScheduleID'])->references(['Id'])->on('t_FleetMaintenanceSchedules')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VehicleID'])->references(['Id'])->on('t_FleetVehicles')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetRepairLogs', function (Blueprint $table) {
            $table->dropForeign('t_fleetrepairlogs_createdby_foreign');
            $table->dropForeign('t_fleetrepairlogs_deletedby_foreign');
            $table->dropForeign('t_fleetrepairlogs_modifiedby_foreign');
            $table->dropForeign('t_fleetrepairlogs_repairtype_foreign');
            $table->dropForeign('t_fleetrepairlogs_scheduleid_foreign');
            $table->dropForeign('t_fleetrepairlogs_vehicleid_foreign');
        });
    }
};
