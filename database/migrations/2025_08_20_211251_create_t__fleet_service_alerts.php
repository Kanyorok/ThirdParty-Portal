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
        Schema::create('t_FleetServiceAlerts', function (Blueprint $table) {
            $table->id('Id');
            $table->string('AlertID')->unique();
            $table->foreignId('VehicleID')->nullable()->constrained('t_FleetVehicles', 'Id');
            $table->foreignId('ScheduleID')->nullable()->constrained('t_FleetMaintenanceSchedules', 'Id');
            $table->foreignId('AlertType')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->date('TriggerDate')->nullable();
            $table->decimal('TriggerMileage')->nullable();
            $table->string('Description')->nullable();
            $table->boolean('IsAcknowledged')->nullable();
            $table->date('AcknowledgedOn')->nullable();
            $table->foreignId('AcknowledgedBy')->nullable()->constrained('t_Users', 'Id');
            $table->foreignId('MaintenanceStatus')->nullable()->constrained('t_CodeDetails', 'ID');
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
        Schema::dropIfExists('t_FleetServiceAlerts');
    }
};
