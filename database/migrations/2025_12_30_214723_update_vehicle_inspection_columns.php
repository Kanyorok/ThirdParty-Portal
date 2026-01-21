<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_FleetVehicleInspections', function (Blueprint $table) {

            $table->dropColumn('Fuel');
            $table->dropColumn('EngineOil');
            $table->dropColumn('Coolant');

            $table->foreignId('Fuel')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->foreignId('EngineOil')->nullable()->constrained('t_CodeDetails', 'ID');
            $table->foreignId('Coolant')->nullable()->constrained('t_CodeDetails', 'ID');
        });
    }

    public function down(): void
    {
        Schema::table('t_FleetVehicleInspections', function (Blueprint $table) {

            $table->dropForeign(['Fuel']);
            $table->dropForeign(['EngineOil']);
            $table->dropForeign(['Coolant']);

            $table->dropColumn('Fuel');
            $table->dropColumn('EngineOil');
            $table->dropColumn('Coolant');

            $table->decimal('Fuel', 10, 2)->default(0);
            $table->decimal('EngineOil', 10, 2)->default(0);
            $table->decimal('Coolant', 10, 2)->default(0);

        });
    }
};
