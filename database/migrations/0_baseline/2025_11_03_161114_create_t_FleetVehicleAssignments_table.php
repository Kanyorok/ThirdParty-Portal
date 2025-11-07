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
        Schema::create('t_FleetVehicleAssignments', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('AssignmentID')->unique();
            $table->bigInteger('TripNo');
            $table->bigInteger('VehicleType');
            $table->integer('DriverID')->nullable();
            $table->bigInteger('VehicleID');
            $table->date('LastInspectionDate')->nullable();
            $table->date('AssignmentDate')->nullable();
            $table->bigInteger('AssignedBy')->nullable();
            $table->string('Purpose');
            $table->string('Notes')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_fleetv__3214ec07e237ef65');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FleetVehicleAssignments');
    }
};
