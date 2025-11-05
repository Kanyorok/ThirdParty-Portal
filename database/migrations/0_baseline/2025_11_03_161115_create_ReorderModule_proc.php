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
        DB::unprepared("--AUTHOR HEZRON BII 
--PURPOSE: REORDER MODULE POSITION
--PARAMS: ModuleID, New position
--rerun this to retore order


CREATE   PROCEDURE dbo.ReorderModule
    @ModuleId BIGINT,
    @NewPos INT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @OldPos INT, @TempPos NVARCHAR(10) = 'X';

    -- Get the current top-level position (the first part of orderKey)

	SELECT 
   @OldPos=
    CAST(LEFT(orderKey, CHARINDEX('.', orderKey + '.') - 1) AS INT)
		FROM dbo.t_Modules
		WHERE ModuleID = @ModuleId;
    IF @OldPos IS NULL
    BEGIN
        RAISERROR('Module not found or not ordered', 16, 1);
        RETURN;
    END;

    -- If no change
    IF @OldPos = @NewPos RETURN;

    -- Temporarily mark the module to move (avoid key collision)
    UPDATE dbo.t_Modules
    SET orderKey = REPLACE(orderKey, CAST(@OldPos AS NVARCHAR), @TempPos)
    WHERE orderKey LIKE CAST(@OldPos AS NVARCHAR) + '%';

    -- Shift the module currently at @NewPos into @OldPos
    UPDATE dbo.t_Modules
    SET orderKey = REPLACE(orderKey, CAST(@NewPos AS NVARCHAR), CAST(@OldPos AS NVARCHAR))
    WHERE orderKey LIKE CAST(@NewPos AS NVARCHAR) + '%';

    -- Replace temp marker with @NewPos
    UPDATE dbo.t_Modules
    SET orderKey = REPLACE(orderKey, @TempPos, CAST(@NewPos AS NVARCHAR))
    WHERE orderKey LIKE @TempPos + '%';

END;
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS ReorderModule");
    }
};
