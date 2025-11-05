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
        Schema::create('t_FleetDrivers', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('DriverNo')->unique();
            $table->string('FullName');
            $table->bigInteger('StaffNumber');
            $table->integer('NationalID');
            $table->string('Phone');
            $table->bigInteger('EmploymentType');
            $table->string('Notes')->nullable();
            $table->boolean('IsActive');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('Email')->nullable();
            $table->bigInteger('ImageId')->nullable();
            $table->bigInteger('DriverStatus')->nullable();

            $table->primary(['Id'], 'pk__t_fleetd__3214ec07a5bfe9e1');
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
