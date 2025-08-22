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
        Schema::create('t_FleetInspectionSchedule', function (Blueprint $table) {
            $table->id('Id');
            $table->string('InspectionNo')->unique();
            $table->foreignId('VehicleID')->constrained('t_FleetVehicles', 'Id');
            $table->string('InspectionType');
            $table->date('InspectionDate');
            $table->date('DueDate');
            $table->foreignId('Status')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('Inspector')->constrained('t_Employees', 'Id');
            $table->string('Remarks');
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
        Schema::dropIfExists('t_FleetInspectionSchedule');
    }
};
