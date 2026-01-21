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
                $table->foreignId('ContractedDriverID')
                    ->nullable()
                    ->after('DriverID')
                    ->constrained('t_ContractedDrivers', 'Id')
                    ->nullOnDelete();

                $table->foreignId('DriverID')
                    ->nullable()
                    ->change();
            });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetVehicleInspections', function (Blueprint $table) {
            if (Schema::hasColumn('t_FleetVehicleInspections', 'ContractedDriverID')) {
                $table->dropForeign(['ContractedDriverID']);
                $table->dropColumn('ContractedDriverID');
            }

            $table->foreignId('DriverID')
                ->nullable(false)
                ->change();
        });
    }
};
