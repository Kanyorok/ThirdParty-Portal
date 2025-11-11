<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds a surrogate identity primary key column `ModelRoleId` to the
     * `t_ModelRoles` table for compatibility with legacy code that expects a single PK.
     *
     * Notes for SQL Server:
     * - Adding an IDENTITY column to an existing table isn't supported directly via ALTER TABLE ADD in SQL Server
     *   when the table already has data and no identity. The migration below uses a safe approach:
     *   1. Create a new temporary table with the desired schema (including IDENTITY ModelRoleId).
     *   2. Copy data from the old table into the temporary table.
     *   3. Drop the old table.
     *   4. Rename the temporary table to the original name.
     *
     * Please backup `t_ModelRoles` before running this migration. Run in staging first.
     */
    public function up(): void
    {
        // Use DB::unprepared to run raw SQL tailored for SQL Server
        $sql = <<<'SQL'
BEGIN TRANSACTION;

-- 1. Create temporary table with ModelRoleId identity
IF OBJECT_ID('tempdb..#t_ModelRoles_backup') IS NOT NULL
    DROP TABLE #t_ModelRoles_backup;

SELECT TOP (0) * INTO #t_ModelRoles_backup FROM t_ModelRoles;

-- Build CREATE TABLE statement dynamically to preserve columns and constraints except identity pk handling.
-- We'll create a permanent table with the same columns and ModelRoleId as INT IDENTITY(1,1)
IF OBJECT_ID('dbo.__t_ModelRoles_new') IS NOT NULL
    DROP TABLE dbo.__t_ModelRoles_new;

CREATE TABLE dbo.__t_ModelRoles_new (
    ModelRoleId INT IDENTITY(1,1) NOT NULL,
    -- The rest of the columns are created as broadly-typed to match existing table; adjust if you have stricter types.
    model_id NVARCHAR(255) NULL,
    model_type NVARCHAR(255) NULL,
    role_id INT NULL,
    BranchId INT NULL,
    CreatedOn DATETIME2 NULL,
    ModifiedOn DATETIME2 NULL,
    DeletedOn DATETIME2 NULL
    -- If your table has additional columns, add them here. This migration assumes common columns used by the app.
);

-- Copy data from the old table to the new table. Identity will auto-populate.
INSERT INTO dbo.__t_ModelRoles_new (model_id, model_type, role_id, BranchId, CreatedOn, ModifiedOn, DeletedOn)
SELECT model_id, model_type, role_id, BranchId, CreatedOn, ModifiedOn, DeletedOn FROM dbo.t_ModelRoles;

-- Drop old table (rename for safety)
EXEC sp_rename 'dbo.t_ModelRoles', 't_ModelRoles_old';

-- Rename new table to original name
EXEC sp_rename 'dbo.__t_ModelRoles_new', 't_ModelRoles';

COMMIT TRANSACTION;
SQL;

        DB::unprepared($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The down step attempts to restore the previous table if present; this is a best-effort rollback.
        $sql = <<<'SQL'
BEGIN TRANSACTION;
IF OBJECT_ID('dbo.t_ModelRoles_old') IS NOT NULL
BEGIN
    -- Drop the current table
    DROP TABLE dbo.t_ModelRoles;
    -- Rename the old table back
    EXEC sp_rename 'dbo.t_ModelRoles_old', 't_ModelRoles';
END
COMMIT TRANSACTION;
SQL;
        DB::unprepared($sql);
    }
};
