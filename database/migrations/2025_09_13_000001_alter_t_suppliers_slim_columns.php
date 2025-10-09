<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_Suppliers', function (Blueprint $table) {
            // Drop FK if it exists
            if (Schema::hasColumn('t_Suppliers', 'CategoryId')) {
                // Drop foreign key constraint first (SQL Server specific name)
                DB::statement('ALTER TABLE t_Suppliers DROP CONSTRAINT t_suppliers_categoryid_foreign');
                $table->dropColumn('CategoryId');
            }

            // Drop other legacy columns if they exist
            $drop = ['SupplierName','ContactEmail','ContactPhone','Address','IsPrequalified'];
            foreach ($drop as $col) {
                if (Schema::hasColumn('t_Suppliers', $col)) {
                    $table->dropColumn($col);
                }
            }

            // Add new columns if they don’t exist
            if (!Schema::hasColumn('t_Suppliers','RoundID')) {
                $table->unsignedBigInteger('RoundID')->nullable()->after('Id');
            }
            if (!Schema::hasColumn('t_Suppliers','ThirdPartyID')) {
                $table->unsignedBigInteger('ThirdPartyID')->nullable()->after('RoundID');
            }
            if (!Schema::hasColumn('t_Suppliers','Active_Status')) {
                $table->boolean('Active_Status')->default(1)->after('ThirdPartyID');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_Suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('t_Suppliers','Active_Status')) {
                $table->dropColumn('Active_Status');
            }
            // Not restoring old columns automatically
        });
    }
};
