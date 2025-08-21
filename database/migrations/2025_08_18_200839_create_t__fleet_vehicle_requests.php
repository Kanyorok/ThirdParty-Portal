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
    Schema::create('t_FleetVehicleRequests', function (Blueprint $table) {
    $table->id('Id');
    $table->string('RequestID');
    $table->foreignId('RequestedBy')->constrained('t_Employees', 'Id');
    $table->foreignId('Department')->constrained('t_Departments', 'Id');
    $table->date('RequestDate');
    $table->foreignId('TripNo')->constrained('t_TripLogs', 'Id');
    $table->date('TripDate');
    $table->string('Purpose');
    $table->string('FromLocation');
    $table->string('ToLocation');
    $table->integer('PassengerCount');
    $table->foreignId('PreferredVehicleType')->constrained('t_CodeDetails', 'Id');
    $table->string('Status')->nullable();
    $table->foreignId('ApprovedBy')->nullable()->constrained('t_Employees', 'Id');
    $table->date('ApprovedOn')->nullable();
    $table->string('RejectionReason')->nullable();
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
        Schema::dropIfExists('t_FleetVehicleRequests');
    }
};
