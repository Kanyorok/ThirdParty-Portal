<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_Suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('t_Suppliers', 'CategoryId')) {
                $table->unsignedBigInteger('CategoryId')->nullable()->after('ThirdPartyID');
            }
            // Add supporting non-unique index for lookups
            if (!Schema::hasColumn('t_Suppliers', 'RoundID')) {
                $table->unsignedBigInteger('RoundID')->nullable()->after('Id');
            }
        });

        // Create a filtered unique index on (RoundID, ThirdPartyID, CategoryId) when CategoryId IS NOT NULL (SQL Server)
        try {
            DB::statement(<<<SQL
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'uq_t_suppliers_round_tp_cat' AND object_id = OBJECT_ID('t_Suppliers'))
BEGIN
    CREATE UNIQUE INDEX uq_t_suppliers_round_tp_cat ON dbo.t_Suppliers (RoundID, ThirdPartyID, CategoryId) WHERE CategoryId IS NOT NULL;
END
SQL
            );
        } catch (\Throwable $e) {
            // Fallback: create a non-unique composite index if filtered unique not supported
            try {
                DB::statement(<<<SQL
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'ix_t_suppliers_round_tp_cat' AND object_id = OBJECT_ID('t_Suppliers'))
BEGIN
    CREATE INDEX ix_t_suppliers_round_tp_cat ON dbo.t_Suppliers (RoundID, ThirdPartyID, CategoryId);
END
SQL
                );
            } catch (\Throwable $ignored) {
            }
        }
    }

    public function down(): void
    {
        try {
            DB::statement(<<<SQL
IF EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'uq_t_suppliers_round_tp_cat' AND object_id = OBJECT_ID('t_Suppliers'))
BEGIN
    DROP INDEX uq_t_suppliers_round_tp_cat ON dbo.t_Suppliers;
END
SQL
            );
        } catch (\Throwable $e) {
        }

        try {
            DB::statement(<<<SQL
IF EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'ix_t_suppliers_round_tp_cat' AND object_id = OBJECT_ID('t_Suppliers'))
BEGIN
    DROP INDEX ix_t_suppliers_round_tp_cat ON dbo.t_Suppliers;
END
SQL
            );
        } catch (\Throwable $e) {
        }

        Schema::table('t_Suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('t_Suppliers', 'CategoryId')) {
                $table->dropColumn('CategoryId');
            }
        });
    }
};
