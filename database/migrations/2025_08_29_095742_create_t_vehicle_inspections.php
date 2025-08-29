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

            $table->id('Id');
            $table->string('InspectionID')->unique();
            $table->foreignId('VehicleID')->constrained('t_FleetVehicles', 'Id');
            $table->foreignId('FuelType')->constrained('t_FuelTypes', 'Id');
            $table->foreignId('DriverID')->constrained('t_FleetDrivers', 'Id');
            $table->dateTime('InspectionDate');
            $table->bigInteger('Mileage')->default(0);      
            $table->decimal('Fuel', 10, 2)->default(0);      
            $table->decimal('EngineOil', 10, 2)->default(0); 
            $table->decimal('Coolant', 10, 2)->default(0);  
            $table->integer('Speedometer')->default(0);      
            $table->boolean('Reflector')->nullable();
            $table->boolean('FireExtinguisher')->nullable();
            $table->boolean('FirstAidKit')->nullable();
            $table->boolean('SpareTyre')->nullable();
            $table->boolean('Spanner')->nullable();
            $table->boolean('Jack')->nullable();
            $table->boolean('4XFloorMats')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');

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
