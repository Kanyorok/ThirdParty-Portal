<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing constraint
        try {
            DB::statement("ALTER TABLE t_PrequalificationRounds DROP CONSTRAINT CK_PrequalificationRounds_Status");
        } catch (\Throwable $e) {
            // Constraint might no longer exist or have a different name in some environments
        }

        // Add the updated constraint including 'E' (Expired)
        DB::statement("ALTER TABLE t_PrequalificationRounds ADD CONSTRAINT CK_PrequalificationRounds_Status CHECK (Status IN ('D','O','CL','E'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original constraint (D, O, CL)
        try {
            DB::statement("ALTER TABLE t_PrequalificationRounds DROP CONSTRAINT CK_PrequalificationRounds_Status");
        } catch (\Throwable $e) {}

        // Note: This will fail if there are records with 'E' status.
        DB::statement("ALTER TABLE t_PrequalificationRounds ADD CONSTRAINT CK_PrequalificationRounds_Status CHECK (Status IN ('D','O','CL'))");
    }
};
