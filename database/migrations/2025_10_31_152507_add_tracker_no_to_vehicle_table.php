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
        Schema::table('t_FleetVehicles', static function (Blueprint $table) {
            $table->string('TrackerNo')->nullable()->after('RegistrationNo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetVehicles', static function (Blueprint $table) {
            $table->dropColumn('TrackerNo');
        });
    }
};
