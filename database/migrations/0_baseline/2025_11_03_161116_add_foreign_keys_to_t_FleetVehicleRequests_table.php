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
        Schema::table('t_FleetVehicleRequests', function (Blueprint $table) {
            $table->foreign(['ApprovedBy'])->references(['Id'])->on('t_Employees')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Department'])->references(['Id'])->on('t_Departments')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PreferredVehicleType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RequestedBy'])->references(['Id'])->on('t_Employees')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TripNo'])->references(['Id'])->on('t_TripLogs')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetVehicleRequests', function (Blueprint $table) {
            $table->dropForeign('t_fleetvehiclerequests_approvedby_foreign');
            $table->dropForeign('t_fleetvehiclerequests_createdby_foreign');
            $table->dropForeign('t_fleetvehiclerequests_deletedby_foreign');
            $table->dropForeign('t_fleetvehiclerequests_department_foreign');
            $table->dropForeign('t_fleetvehiclerequests_modifiedby_foreign');
            $table->dropForeign('t_fleetvehiclerequests_preferredvehicletype_foreign');
            $table->dropForeign('t_fleetvehiclerequests_requestedby_foreign');
            $table->dropForeign('t_fleetvehiclerequests_tripno_foreign');
        });
    }
};
