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
        Schema::table('t_FleetVehicles', function (Blueprint $table) {
            $table->decimal('MaxLoad', 10, 2)->nullable();
            $table->integer('MaxPassengers')->nullable();
            $table->string('Color', 15)->nullable();
            $table->foreignId('ImageId')->nullable()->constrained('t_Images', 'ImageID');
            $table->foreignId('VehicleStatus')->nullable()->constrained('t_CodeDetails', 'ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetVehicles', function (Blueprint $table) {
            $table->dropColumn('MaxLoad');
            $table->dropColumn('MaxPassengers');
            $table->dropColumn('Color');
            $table->dropForeign(['ImageId']);
            $table->dropColumn('ImageId');
            $table->dropForeign(['VehicleStatus']);
            $table->dropColumn('VehicleStatus');
            
        });
    }
};
