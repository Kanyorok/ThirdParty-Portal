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
        Schema::table('t_FleetDrivers', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DriverStatus'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['EmploymentType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ImageId'])->references(['ImageID'])->on('t_Images')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['StaffNumber'])->references(['Id'])->on('t_Employees')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetDrivers', function (Blueprint $table) {
            $table->dropForeign('t_fleetdrivers_createdby_foreign');
            $table->dropForeign('t_fleetdrivers_deletedby_foreign');
            $table->dropForeign('t_fleetdrivers_driverstatus_foreign');
            $table->dropForeign('t_fleetdrivers_employmenttype_foreign');
            $table->dropForeign('t_fleetdrivers_imageid_foreign');
            $table->dropForeign('t_fleetdrivers_modifiedby_foreign');
            $table->dropForeign('t_fleetdrivers_staffnumber_foreign');
        });
    }
};
