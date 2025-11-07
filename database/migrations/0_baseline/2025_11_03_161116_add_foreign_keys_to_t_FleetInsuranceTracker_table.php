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
        Schema::table('t_FleetInsuranceTracker', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['InsuranceProvider'])->references(['Id'])->on('t_InsuranceProviders')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Status'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VehicleID'])->references(['Id'])->on('t_FleetVehicles')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetInsuranceTracker', function (Blueprint $table) {
            $table->dropForeign('t_fleetinsurancetracker_createdby_foreign');
            $table->dropForeign('t_fleetinsurancetracker_deletedby_foreign');
            $table->dropForeign('t_fleetinsurancetracker_insuranceprovider_foreign');
            $table->dropForeign('t_fleetinsurancetracker_modifiedby_foreign');
            $table->dropForeign('t_fleetinsurancetracker_status_foreign');
            $table->dropForeign('t_fleetinsurancetracker_vehicleid_foreign');
        });
    }
};
