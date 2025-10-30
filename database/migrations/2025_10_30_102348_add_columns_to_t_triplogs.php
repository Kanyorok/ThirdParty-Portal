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

            $table->foreignId('Status')->nullable()->after('Notes')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('PreTripInspectionID')->nullable()->after('Status')->constrained('t_FleetVehicleInspections', 'Id');
            $table->foreignId('PostTripInspectionID')->nullable()->after('PreTripInspectionID')->constrained('t_FleetVehicleInspections', 'Id');
            $table->foreignId('StartMileage')->nullable()->after('PostTripInspectionID')->constrained('t_FleetVehicleInspections', 'Id');
            $table->foreignId('EndMileage')->nullable()->after('StartMileage')->constrained('t_FleetVehicleInspections', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TripLogs', function (Blueprint $table) {
            $table->dropForeign(['Status']);
            $table->dropForeign(['PreTripInspectionID']);
            $table->dropForeign(['PostTripInspectionID']);
            $table->dropForeign(['StartMileage']);
            $table->dropForeign(['EndMileage']);

            $table->dropColumn([
                'Status',
                'PreTripInspectionID',
                'PostTripInspectionID',
                'StartMileage',
                'EndMileage',
            ]);
        });
    }
};
