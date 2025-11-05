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
        Schema::create('t_TripLogs', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('TripNo')->unique();
            $table->date('TripStartDate');
            $table->time('StartTime', 7)->nullable();
            $table->date('TripEndDate');
            $table->time('EndTime', 7)->nullable();
            $table->string('StartLocation')->nullable();
            $table->string('EndLocation')->nullable();
            $table->string('Route')->nullable();
            $table->decimal('DistanceCovered')->nullable();
            $table->string('Purpose')->nullable();
            $table->string('Notes')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('TripType')->nullable();
            $table->string('TripCode')->nullable();
            $table->bigInteger('VehicleType')->nullable();
            $table->bigInteger('LoadType')->nullable();
            $table->bigInteger('ParentTripID')->nullable();
            $table->bigInteger('ApprovedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->bigInteger('Status')->nullable();

            $table->primary(['Id'], 'pk__t_triplo__3214ec0758b1a1fe');
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
