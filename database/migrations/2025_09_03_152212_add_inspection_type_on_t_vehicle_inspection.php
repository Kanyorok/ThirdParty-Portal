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
        Schema::table('t_FleetVehicleInspections', function (Blueprint $table) {
            $table->foreignId('InspectionTypeID')->nullable()->constrained('t_CodeDetails', 'Id')->after('InspectionID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetVehicleInspections', function (Blueprint $table) {
            $table->dropForeign(['InspectionTypeID']);
            $table->dropColumn('InspectionTypeID');
        });
    }
};
