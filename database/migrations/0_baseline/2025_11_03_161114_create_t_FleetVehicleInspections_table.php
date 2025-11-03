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
        Schema::create('t_FleetVehicleInspections', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('InspectionID')->unique();
            $table->bigInteger('ParentInspectionID')->nullable();
            $table->bigInteger('VehicleID');
            $table->bigInteger('FuelType');
            $table->bigInteger('DriverID');
            $table->date('InspectionDate');
            $table->bigInteger('Mileage')->default(0);
            $table->decimal('Fuel', 10)->default(0);
            $table->decimal('EngineOil', 10)->default(0);
            $table->decimal('Coolant', 10)->default(0);
            $table->boolean('Reflector')->nullable();
            $table->boolean('FireExtinguisher')->nullable();
            $table->boolean('FirstAidKit')->nullable();
            $table->boolean('SpareTyre')->nullable();
            $table->boolean('Spanner')->nullable();
            $table->boolean('Jack')->nullable();
            $table->boolean('4XFloorMats')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('InspectionTypeID')->nullable();
            $table->integer('SourceID')->nullable();

            $table->primary(['Id'], 'pk__t_fleetv__3214ec0757d420ec');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FleetVehicleInspections');
    }
};
