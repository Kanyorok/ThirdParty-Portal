<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_FleetVehicleAssignments', function (Blueprint $table) {
            $table->id('Id');
            $table->string('AssignmentID')->unique();
            $table->foreignId('TripNo')->constrained('t_TripLogs', 'Id')->nullable();
            $table->foreignId('VehicleType')->constrained('t_CodeDetails', 'ID');
            $table->integer('DriverID')->nullable();
            $table->foreignId('VehicleID')->constrained('t_FleetVehicles', 'Id');
            $table->date('LastInspectionDate')->nullable();
            $table->date('AssignmentDate')->nullable();
            $table->foreignId('AssignedBy')->nullable()->constrained('t_Employees', 'Id');
            $table->string('Purpose');
            $table->string('Notes')->nullable();
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
        Schema::dropIfExists('t_FleetVehicleAssignments');
    }
};
