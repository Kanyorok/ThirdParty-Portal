<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_FinanceCreditManagement', function (Blueprint $table) {
            // Drop FK to t_TenantMaintenance on CustomerID (if it exists)
            try {
                $table->dropForeign(['CustomerID']);
            } catch (\Throwable $e) {
                // Some drivers require explicit name; ignore if already dropped
            }
        });

        // Make column nullable (SQL Server syntax)
        try {
            DB::statement('ALTER TABLE t_FinanceCreditManagement ALTER COLUMN CustomerID BIGINT NULL');
        } catch (\Throwable $e) {
            // ignore if already nullable or platform differs
        }
    }

    public function down(): void
    {
        Schema::table('t_FinanceCreditManagement', function (Blueprint $table) {
            // Restore FK to t_TenantMaintenance (optional)
            try {
                $table->foreign('CustomerID')->references('Id')->on('t_TenantMaintenance');
            } catch (\Throwable $e) {
                // Ignore if table/constraint missing
            }
        });

        // Optionally revert to NOT NULL
        try {
            DB::statement('ALTER TABLE t_FinanceCreditManagement ALTER COLUMN CustomerID BIGINT NOT NULL');
        } catch (\Throwable $e) {
            // ignore
        }
    }
};


