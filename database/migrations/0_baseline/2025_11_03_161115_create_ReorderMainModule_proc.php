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
        DB::unprepared("
CREATE   PROCEDURE dbo.ReorderMainModule
    @ModuleId INT,
    @NewPosStr VARCHAR(50)   -- new desired orderKey prefix (e.g. '1', '2.3', '10')
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @OldPosStr VARCHAR(50);
    DECLARE @NewTopSeg INT;
    DECLARE @OldTopSeg INT;

    -- 1. Get the current orderKey of the selected module
    SELECT @OldPosStr = orderKey FROM dbo.t_Modules WHERE ModuleID = @ModuleId;
    IF @OldPosStr IS NULL
    BEGIN
        RAISERROR('ModuleID %d not found or has NULL orderKey', 16, 1, @ModuleId);
        RETURN;
    END;

    -- 2. Get the top-level segment of the new and old orderKey
    SET @NewTopSeg = CAST(LEFT(@NewPosStr, CHARINDEX('.', @NewPosStr + '.') - 1) AS INT);
    SET @OldTopSeg = CAST(LEFT(@OldPosStr, CHARINDEX('.', @OldPosStr + '.') - 1) AS INT);

    -- 3. Use a temp table to store all modules to shift (excluding the moving module and its subs)
    IF OBJECT_ID('tempdb..#ToShift') IS NOT NULL DROP TABLE #ToShift;
    CREATE TABLE #ToShift (ModuleID INT, Orderkey VARCHAR(100));

    INSERT INTO #ToShift (ModuleID, Orderkey)
    SELECT ModuleID, orderKey
    FROM dbo.t_Modules
    WHERE CAST(LEFT(orderKey, CHARINDEX('.', orderKey + '.') - 1) AS INT) >= @NewTopSeg
      AND (orderKey NOT LIKE @OldPosStr + '%' OR orderKey = @OldPosStr)
      AND ModuleID <> @ModuleId;

    -- 4. Shift all affected modules and their submodules down by 1
    UPDATE m
    SET orderKey =
        CAST(CAST(LEFT(m.orderKey, CHARINDEX('.', m.orderKey + '.') - 1) AS INT) + 1 AS VARCHAR(10))
        + SUBSTRING(m.orderKey, CHARINDEX('.', m.orderKey + '.'), 100)
    FROM dbo.t_Modules m
    INNER JOIN #ToShift t ON m.ModuleID = t.ModuleID;

    -- 5. Move the selected module and its submodules to the new position
    UPDATE dbo.t_Modules
    SET orderKey = @NewPosStr + SUBSTRING(orderKey, LEN(@OldPosStr) + 1, 100)
    WHERE orderKey = @OldPosStr OR orderKey LIKE @OldPosStr + '.%';

END;
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS ReorderMainModule");
    }
};
