<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_FleetMaintenanceSchedules', function (Blueprint $table) {
            $table->dropColumn('Location');
        });

         Schema::table('t_FleetMaintenanceSchedules', function (Blueprint $table) {
            $table->foreignId('VendorID')->nullable()->constrained('t_SupplierMaster', 'Id');
        });
    }

    public function down(): void
    {
        Schema::table('t_FleetMaintenanceSchedules', function (Blueprint $table) {

            $table->dropForeign('VendorID');
            $table->dropColumn('VendorID');
            
        });

            Schema::table('t_FleetMaintenanceSchedules', function (Blueprint $table) {
                $table->string('Location')->nullable();
            });
    }
};
