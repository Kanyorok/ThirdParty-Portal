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
        Schema::table('t_FleetRepairLogs', function (Blueprint $table) {
            if (Schema::hasColumn('t_FleetRepairLogs', 'Vendor')) {
                $table->dropColumn('Vendor');
            }

            $table->foreignId('VendorID')
                ->nullable()->constrained('t_ThirdParties', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FleetRepairLogs', function (Blueprint $table) {
            $table->dropForeign(['VendorID']);
            $table->dropColumn('VendorID');

            $table->string('Vendor')->nullable()->after('RepairDate');
        });
    }
};
