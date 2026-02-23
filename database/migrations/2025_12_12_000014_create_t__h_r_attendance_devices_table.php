<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRAttendanceDevices', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DeviceCode', 100)->unique();
            $table->string('Name', 150);
            $table->string('Channel', 50); // Android, Biometric, Web
            $table->string('AllowedIPs', 500)->nullable();
            $table->string('AllowedLocations', 500)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRAttendanceDevices');
    }
};
