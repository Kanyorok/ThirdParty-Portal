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

            $table->id('Id');
            $table->string('RegistrationNo')->unique();
            $table->foreignId('Make')->constrained('t_FleetBrands', 'Id');
            $table->foreignId('Model')->constrained('t_FleetModels', 'Id');
            $table->foreignId('VehicleType')->constrained('t_CodeDetails', 'ID');        
            $table->integer('YearOfManufacture')->nullable();
            $table->string('ChassisNo')->unique();
            $table->string('EngineNo')->nullable();
            $table->foreignId('FuelType')->constrained('t_FuelTypes', 'Id');
            $table->string('Capacity')->nullable();
            $table->decimal('OdometerReading', 10, 2)->default(0);
            $table->foreignId('AssignedBranch')->nullable()->constrained('t_Branches', 'Id');
            $table->foreignId('Status')->constrained('t_CodeDetails', 'ID');
            $table->boolean('IsActive')->nullable();
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
        Schema::dropIfExists('t_FleetVehicles');
    }
};
