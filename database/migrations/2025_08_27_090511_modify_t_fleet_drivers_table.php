<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_FleetDriverAssignments', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['DriverID']);
            // Add the new foreign key constraint only
            $table->foreign('DriverID')->references('Id')->on('t_FleetDrivers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('t_FleetDriverAssignments', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['DriverID']);
            // Restore the previous foreign key constraint only
            $table->foreign('DriverID')->references('Id')->on('t_ContractedDrivers')->nullOnDelete();
        });
    }

};
