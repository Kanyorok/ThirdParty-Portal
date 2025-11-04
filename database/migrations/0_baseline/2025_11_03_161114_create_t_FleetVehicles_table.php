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
        Schema::create('t_FleetVehicles', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('RegistrationNo')->unique();
            $table->bigInteger('Make');
            $table->bigInteger('Model');
            $table->bigInteger('VehicleType');
            $table->integer('YearOfManufacture')->nullable();
            $table->string('ChassisNo')->unique();
            $table->string('EngineNo')->nullable();
            $table->bigInteger('FuelType');
            $table->string('Capacity')->nullable();
            $table->decimal('OdometerReading', 10)->default(0);
            $table->bigInteger('AssignedBranch')->nullable();
            $table->bigInteger('Status');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->decimal('MaxLoad', 10)->nullable();
            $table->integer('MaxPassengers')->nullable();
            $table->string('Color', 15)->nullable();
            $table->bigInteger('ImageId')->nullable();
            $table->bigInteger('VehicleStatus')->nullable();
            $table->string('TrackerNo')->nullable();

            $table->primary(['Id'], 'pk__t_fleetv__3214ec070403c5df');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FleetVehicles');
    }
};
