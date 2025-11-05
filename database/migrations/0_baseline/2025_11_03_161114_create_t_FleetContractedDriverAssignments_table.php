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
            $table->bigIncrements('Id');
            $table->bigInteger('DriverID')->nullable();
            $table->bigInteger('VehicleID')->nullable();
            $table->date('AssignmentDate')->nullable();
            $table->date('UnassignmentDate')->nullable();
            $table->string('Purpose')->nullable();
            $table->string('Notes')->nullable();
            $table->bigInteger('AssignedBy')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_fleetc__3214ec07fbae5b3a');
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
