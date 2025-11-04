<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("--SELECT ModuleID, Name, ParentId,orderKey,orderKey FROM t_Modules


--begin tran
CREATE   PROCEDURE dbo.InitializeModuleOrder
AS
BEGIN
    SET NOCOUNT ON;

    ;WITH Hier AS (
        -- anchors: root nodes (ParentId IS NULL)
        SELECT
            ModuleID,
            ParentId,
            CAST(RIGHT('000000' + CAST(ModuleID AS VARCHAR(12)), 6) AS VARCHAR(MAX)) AS path
        FROM dbo.t_Modules
        WHERE ParentId IS NULL

        UNION ALL

        SELECT
            m.ModuleID,
            m.ParentId,
            h.path + '.' + RIGHT('000000' + CAST(m.ModuleID AS VARCHAR(12)), 6)
        FROM dbo.t_Modules m
        INNER JOIN Hier h ON m.ParentId = h.ModuleID
    ),
    Ordered AS (
        SELECT
            ModuleID,
            ParentId,
            path,
            ROW_NUMBER() OVER (ORDER BY path) AS NewOrder
        FROM Hier
    )
    -- Update the table with the computed preorder NewOrder
    UPDATE t
    SET orderKey = o.NewOrder
    FROM dbo.t_Modules t
    INNER JOIN Ordered o ON t.ModuleID = o.ModuleID;
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS InitializeModuleOrder");
    }
};
