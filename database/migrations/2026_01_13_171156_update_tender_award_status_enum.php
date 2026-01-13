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
        // 1. Find and drop the existing constraint dynamically
        $table = 't_TenderAwards';
        $column = 'AwardStatus';
        
        // This query finds the check constraint name for a specific table and column in SQL Server
        $sql = "
            SELECT k.name
            FROM sys.check_constraints k
            JOIN sys.tables t ON k.parent_object_id = t.object_id
            JOIN sys.all_columns c ON c.object_id = t.object_id AND c.column_id = k.parent_column_id
            WHERE t.name = ? AND c.name = ?
        ";
        
        $results = \Illuminate\Support\Facades\DB::select($sql, [$table, $column]);
        
        foreach ($results as $result) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$result->name}");
        }

        // 2. Add the new constraint with all required values
        // Values: Draft, Pending, Submitted for Approval, Under Review, Approved, Rejected, Cancelled
        $constraintName = "CK_{$table}_{$column}_V2";
        $checkDefinition = "AwardStatus IN ('Draft', 'Pending', 'Submitted for Approval', 'Under Review', 'Approved', 'Rejected', 'Cancelled')";
        
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$constraintName} CHECK ({$checkDefinition})");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ideally we would revert to the old constraint, but we might have data that violates it now.
        // So we will just leave it or strictly revert if needed.
        // For now, we will just try to revert to 'Pending', 'Approved', 'Rejected', 'Cancelled' if possible
        /*
        $table = 't_TenderAwards';
        $column = 'AwardStatus';
        $constraintName = "CK_{$table}_{$column}_V2";
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$constraintName}");
        
        // Restore old (approximate)
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD CONSTRAINT CK_{$table}_{$column}_Original CHECK (AwardStatus IN ('Pending', 'Approved', 'Rejected', 'Cancelled'))");
        */
    }
};
