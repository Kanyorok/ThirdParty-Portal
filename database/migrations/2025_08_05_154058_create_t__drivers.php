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
        Schema::create('t_Drivers', function (Blueprint $table) {

            $table->id('Id');
            $table->string('DriverID')->unique();
            $table->string('DriverName');
            $table->string('LicenseNumber')->unique();
            $table->date('LicenseExpiryDate');
            $table->foreignId('EmploymentStatus')->constrained('t_CodeDetails', 'ID');
            $table->string('Phone')->nullable();
            $table->string('Email')->nullable();
            $table->text('Remarks')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Drivers');
    }
};
