<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_FleetVehicles', function (Blueprint $table) {
            $table->dropColumn('VehicleStatus');

            $table->foreignId('VehicleStatus')->nullable()->constrained('t_CodeDetails', 'ID');
        });
    }

    public function down(): void
    {
        Schema::table('t_FleetVehicles', function (Blueprint $table) {
            $table->dropForeign(['VehicleStatus']);
            $table->dropColumn('VehicleStatus');
            $table->string('VehicleStatus')->nullable();
        });
    }
};
