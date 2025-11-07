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
        Schema::table('t_FleetVehicles', function (Blueprint $table) {
            $table->foreign(['AssignedBranch'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FuelType'])->references(['Id'])->on('t_FuelTypes')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ImageId'])->references(['ImageID'])->on('t_Images')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Make'])->references(['Id'])->on('t_FleetBrands')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Model'])->references(['Id'])->on('t_FleetModels')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Status'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VehicleStatus'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VehicleType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetVehicles', function (Blueprint $table) {
            $table->dropForeign('t_fleetvehicles_assignedbranch_foreign');
            $table->dropForeign('t_fleetvehicles_createdby_foreign');
            $table->dropForeign('t_fleetvehicles_deletedby_foreign');
            $table->dropForeign('t_fleetvehicles_fueltype_foreign');
            $table->dropForeign('t_fleetvehicles_imageid_foreign');
            $table->dropForeign('t_fleetvehicles_make_foreign');
            $table->dropForeign('t_fleetvehicles_model_foreign');
            $table->dropForeign('t_fleetvehicles_modifiedby_foreign');
            $table->dropForeign('t_fleetvehicles_status_foreign');
            $table->dropForeign('t_fleetvehicles_vehiclestatus_foreign');
            $table->dropForeign('t_fleetvehicles_vehicletype_foreign');
        });
    }
};
