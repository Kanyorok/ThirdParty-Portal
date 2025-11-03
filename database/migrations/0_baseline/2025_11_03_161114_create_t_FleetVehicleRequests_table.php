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
            $table->bigIncrements('Id');
            $table->string('RequestID');
            $table->bigInteger('RequestedBy');
            $table->bigInteger('Department');
            $table->date('RequestDate');
            $table->bigInteger('TripNo');
            $table->date('TripDate');
            $table->string('Purpose');
            $table->string('FromLocation');
            $table->string('ToLocation');
            $table->integer('PassengerCount');
            $table->bigInteger('PreferredVehicleType');
            $table->string('Status')->nullable();
            $table->bigInteger('ApprovedBy')->nullable();
            $table->date('ApprovedOn')->nullable();
            $table->string('RejectionReason')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_fleetv__3214ec072ecda206');
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
