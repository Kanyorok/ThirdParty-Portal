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
        Schema::create('t_FleetRepairLogs', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('RepairID')->unique();
            $table->bigInteger('VehicleID')->nullable();
            $table->bigInteger('ScheduleID')->nullable();
            $table->bigInteger('RepairType')->nullable();
            $table->date('RepairDate');
            $table->string('Vendor');
            $table->decimal('Cost');
            $table->date('RenewalDate')->nullable();
            $table->string('Description')->nullable();
            $table->string('Notes')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_fleetr__3214ec076fa4e87f');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FleetRepairLogs');
    }
};
