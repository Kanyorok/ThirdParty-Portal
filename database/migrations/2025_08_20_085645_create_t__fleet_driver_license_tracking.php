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
        Schema::create('t_FleetDriverLicenseTracking', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('DriverID')->nullable()->constrained('t_FleetDrivers', 'Id');
            $table->string('LicenseNumber');
            $table->string('LicenseCategory');
            $table->date('IssueDate');
            $table->date('ExpiryDate');
            $table->date('RenewalDate')->nullable();
            $table->string('Notes')->nullable();
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
        Schema::dropIfExists('t_FleetDriverLicenseTracking');
    }
};
