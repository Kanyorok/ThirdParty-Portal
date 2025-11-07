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
        Schema::table('t_TripLogs', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['DriverID']);
            // Change column to integer, no constraint
            $table->integer('DriverID')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TripLogs', function (Blueprint $table) {
            // Change back to foreignId and add constraint
            $table->unsignedBigInteger('DriverID')->change();
            $table->foreign('DriverID')->references('Id')->on('t_ContractedDrivers');
        });
    }
};
