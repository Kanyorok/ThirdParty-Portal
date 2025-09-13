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
            // Add new fields
            $table->string('TripType')->after('TripNo');
            $table->string('TripCode')->after('TripType');
            $table->foreignId('VehicleType')->after('TripCode')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('LoadType')->after('VehicleType')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('ParentTripID')->nullable()->after('Id')->constrained('t_TripLogs', 'Id');

            $table->string('Purpose')->nullable()->change();

            $table->dropColumn('DriverID');
            $table->dropConstrainedForeignId('DriverType');
            $table->dropConstrainedForeignId('VehicleID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TripLogs', function (Blueprint $table) {
            $table->dropColumn(['TripType', 'TripCode']);
            $table->dropConstrainedForeignId('VehicleType');
            $table->dropConstrainedForeignId('LoadType');
            $table->dropConstrainedForeignId('ParentTripID');

            $table->foreignId('DriverType')->constrained('t_CodeDetails', 'ID');
            $table->string('DriverID')->nullable();
            $table->foreignId('VehicleID')->constrained('t_FleetVehicles', 'Id');

            $table->string('Purpose')->nullable(false)->change();
        });
    }
};
