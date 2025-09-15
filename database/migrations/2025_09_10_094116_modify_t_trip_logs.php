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
        Schema::disableForeignKeyConstraints();
 
        Schema::table('t_TripLogs', function (Blueprint $table) {
            // New fields
            $table->string('TripType')->after('TripNo')->nullable();
            $table->string('TripCode')->after('TripType')->nullable();
 
            $table->unsignedBigInteger('VehicleType')->nullable()->after('TripCode');
            $table->foreign('VehicleType')->references('ID')->on('t_CodeDetails');
 
            $table->unsignedBigInteger('LoadType')->nullable()->after('VehicleType');
            $table->foreign('LoadType')->references('ID')->on('t_CodeDetails');
 
            $table->unsignedBigInteger('ParentTripID')->nullable()->after('Id');
            $table->foreign('ParentTripID')->references('Id')->on('t_TripLogs');
 
            // Modify existing
            $table->string('Purpose')->nullable()->change();
 
            // Remove old fields
            $table->dropColumn('DriverID');
            $table->dropConstrainedForeignId('DriverType');
            $table->dropConstrainedForeignId('VehicleID');
        });
 
        Schema::enableForeignKeyConstraints();
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
 
        Schema::table('t_TripLogs', function (Blueprint $table) {
            // Drop new constraints first
            $table->dropForeign(['VehicleType']);
            $table->dropForeign(['LoadType']);
            $table->dropForeign(['ParentTripID']);
 
            // Drop new fields
            $table->dropColumn(['TripType', 'TripCode', 'VehicleType', 'LoadType', 'ParentTripID']);
 
            // Recreate old fields
            $table->foreignId('DriverType')->constrained('t_CodeDetails', 'ID');
            $table->string('DriverID')->nullable();
            $table->foreignId('VehicleID')->constrained('t_FleetVehicles', 'Id');
 
            $table->string('Purpose')->nullable(false)->change();
        });
 
        Schema::enableForeignKeyConstraints();
    }
};
 
