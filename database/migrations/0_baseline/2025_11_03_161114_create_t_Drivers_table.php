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
            $table->bigIncrements('Id');
            $table->string('DriverID')->unique();
            $table->string('DriverName');
            $table->string('LicenseNumber')->unique();
            $table->date('LicenseExpiryDate');
            $table->bigInteger('EmploymentStatus');
            $table->string('Phone')->nullable();
            $table->string('Email')->nullable();
            $table->text('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_driver__3214ec074ba95464');
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
