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
        Schema::create('t_FleetContractedDriverAssignments', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('DriverID')->nullable()->constrained('t_ContractedDrivers', 'Id');
            $table->foreignId('VehicleID')->nullable()->constrained('t_FleetVehicles', 'Id');
            $table->date('AssignmentDate')->nullable();
            $table->date('UnassignmentDate')->nullable();
            $table->string('Purpose')->nullable();
            $table->string('Notes')->nullable();
            $table->foreignId('AssignedBy')->nullable()->constrained('t_Employees', 'Id');
            $table->foreignId('CreatedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn')->nullable();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FleetContractedDriverAssignments');
    }
};
