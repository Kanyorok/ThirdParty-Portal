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
        Schema::create('t_FleetInsuranceTracker', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('InsuranceNo')->unique();
            $table->bigInteger('VehicleID');
            $table->bigInteger('InsuranceProvider');
            $table->string('PolicyNumber')->nullable();
            $table->date('CoverageStartDate');
            $table->date('CoverageEndDate');
            $table->decimal('PremiumAmount', 10);
            $table->date('RenewalReminderDate');
            $table->string('Notes')->nullable();
            $table->bigInteger('Status');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_fleeti__3214ec07ab488edc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FleetInsuranceTracker');
    }
};
