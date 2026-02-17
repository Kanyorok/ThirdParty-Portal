<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_HRAttendanceLogs', function (Blueprint $table) {
            $table->id('Id');

            $table->unsignedBigInteger('EmployeeID');
            $table->string('LogType', 20); // ClockIn, ClockOut
            $table->dateTime('LogTime');

            $table->string('Channel', 50)->nullable(); // AndroidApp, Biometric, WebManual
            $table->string('DeviceID', 100)->nullable();
            $table->decimal('Latitude', 10, 7)->nullable();
            $table->decimal('Longitude', 10, 7)->nullable();

            $table->boolean('IsProcessed')->default(0);
            $table->string('Remarks', 255)->nullable();

            $table->unsignedBigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();

            $table->foreign('EmployeeID')->references('Id')->on('t_HREmployees');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_HRAttendanceLogs');
    }
};
