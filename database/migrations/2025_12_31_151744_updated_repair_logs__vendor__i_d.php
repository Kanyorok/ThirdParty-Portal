<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_FleetRepairLogs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('VendorID');
        });

         Schema::table('t_FleetRepairLogs', function (Blueprint $table) {
            $table->foreignId('VendorID')->nullable()->constrained('t_SupplierMaster', 'Id');
        });
    }

    public function down(): void
    {
        Schema::table('t_FleetRepairLogs', function (Blueprint $table) {

            $table->dropForeign(['VendorID']);
            $table->dropColumn('VendorID');
            $table->foreignId('VendorID')
                ->nullable()->constrained('t_ThirdParties', 'Id');

        });
    }
};
