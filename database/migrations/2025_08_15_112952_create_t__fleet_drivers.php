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
        Schema::create('t_FleetDrivers', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DriverNo')->unique();
            $table->string('FullName');
            $table->foreignId('StaffNumber')->constrained('t_Employees', 'Id');
            $table->integer('NationalID');
            $table->string('Phone');
            $table->string('LicenseNumber');
            $table->date('LicenseExpiryDate');
            $table->foreignId('EmploymentType')->constrained('t_CodeDetails', 'ID');
            $table->string('Notes');
            $table->boolean('IsActive');
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
        Schema::dropIfExists('t_FleetDrivers');
    }
};
