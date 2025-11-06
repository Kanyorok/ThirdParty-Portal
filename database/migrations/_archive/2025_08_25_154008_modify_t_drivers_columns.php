<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_FleetDrivers', function (Blueprint $table) {
            $table->dropColumn('LicenseNumber');
            $table->dropColumn('LicenseExpiryDate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetDrivers', function (Blueprint $table) {
            $table->string('LicenseNumber');
            $table->date('LicenseExpiryDate');
        });
    }
};
