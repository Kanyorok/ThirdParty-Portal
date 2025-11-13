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
        // Drop the index first (SQL Server requires this)
        DB::statement('DROP INDEX IF EXISTS t_sysintegrations_integration_index ON t_SYSIntegrations');
        
        // Alter the Integration column from char(3) to char(4) to accommodate CRDB integration
        DB::statement('ALTER TABLE t_SYSIntegrations ALTER COLUMN Integration char(4)');
        
        // Recreate the index
        DB::statement('CREATE INDEX t_sysintegrations_integration_index ON t_SYSIntegrations (Integration)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the index first
        DB::statement('DROP INDEX IF EXISTS t_sysintegrations_integration_index ON t_SYSIntegrations');
        
        // Revert back to char(3) - Note: This will fail if any 4-character integration exists
        DB::statement('ALTER TABLE t_SYSIntegrations ALTER COLUMN Integration char(3)');
        
        // Recreate the index
        DB::statement('CREATE INDEX t_sysintegrations_integration_index ON t_SYSIntegrations (Integration)');
    }
};
