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
            $table->bigIncrements('Id');
            $table->string('InspectionNo')->unique();
            $table->bigInteger('VehicleID');
            $table->string('InspectionType');
            $table->date('InspectionDate');
            $table->date('DueDate');
            $table->bigInteger('Status');
            $table->bigInteger('Inspector');
            $table->string('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_fleeti__3214ec07a2adb9f1');
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
