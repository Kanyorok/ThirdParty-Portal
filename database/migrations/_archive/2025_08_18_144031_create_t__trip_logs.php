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
        Schema::create('t_TripLogs', function (Blueprint $table) {
            $table->id('Id');
            $table->string('TripNo')->unique();
            $table->foreignId('VehicleID')->constrained('t_FleetVehicles', 'Id');
            $table->foreignId('DriverType')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('DriverID')->constrained('t_ContractedDrivers', 'Id');
            $table->date('TripStartDate');
            $table->time('StartTime')->nullable();
            $table->date('TripEndDate');
            $table->time('EndTime')->nullable();
            $table->string('StartLocation')->nullable();
            $table->string('EndLocation')->nullable();
            $table->string('Route')->nullable();
            $table->decimal('DistanceCovered', 8, 2)->nullable();
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
        Schema::dropIfExists('t_TripLogs');
    }
};
