<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sql = <<<'SQL'
-- Add primary key constraint to ModelRoleId if not already present
IF NOT EXISTS (
    SELECT 1 FROM sys.indexes idx
    JOIN sys.index_columns ic ON idx.object_id = ic.object_id AND idx.index_id = ic.index_id
    JOIN sys.columns col ON ic.object_id = col.object_id AND ic.column_id = col.column_id
    WHERE idx.object_id = OBJECT_ID('dbo.t_ModelRoles') AND col.name = 'ModelRoleId'
)
BEGIN
    ALTER TABLE dbo.t_ModelRoles ADD CONSTRAINT PK_t_ModelRoles_ModelRoleId PRIMARY KEY CLUSTERED (ModelRoleId);
END
SQL;
        DB::unprepared($sql);
    }

    public function down(): void
    {
        $sql = <<<'SQL'
IF EXISTS (SELECT 1 FROM sys.indexes idx
    JOIN sys.index_columns ic ON idx.object_id = ic.object_id AND idx.index_id = ic.index_id
    JOIN sys.columns col ON ic.object_id = col.object_id AND ic.column_id = col.column_id
    WHERE idx.object_id = OBJECT_ID('dbo.t_ModelRoles') AND col.name = 'ModelRoleId')
BEGIN
    ALTER TABLE dbo.t_ModelRoles DROP CONSTRAINT PK_t_ModelRoles_ModelRoleId;
END
SQL;
        DB::unprepared($sql);
    }
};
