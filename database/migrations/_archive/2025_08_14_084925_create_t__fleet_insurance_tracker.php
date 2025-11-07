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
        Schema::create('t_FleetInsuranceTracker', function (Blueprint $table) {

            $table->id('Id');
            $table->string('InsuranceNo')->unique();
            $table->foreignId('VehicleID')->constrained('t_FleetVehicles', 'Id');
            $table->foreignId('InsuranceProvider')->constrained('t_InsuranceProviders', 'Id');
            $table->string('PolicyNumber')->nullable();
            $table->date('CoverageStartDate');
            $table->date('CoverageEndDate');
            $table->decimal('PremiumAmount');
            $table->date('RenewalReminderDate');
            $table->string('DocumentPath')->nullable();
            $table->string('Notes');
            $table->foreignId('Status')->constrained('t_CodeDetails', 'ID');
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
        Schema::dropIfExists('t_FleetInsuranceTracker');
    }
};
