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
        DB::unprepared("CREATE   PROC [dbo].[r_TenderCategories] @FromDate smalldatetime= null,
                                                @ToDate smalldatetime=null,
                                                @TenderCategory VARCHAR(100) =null
AS
BEGIN
    CREATE TABLE #tendercategories
    (
        CategoryCode   VARCHAR(100),
        TenderCategory VARCHAR(100),
        Description    VARCHAR(200),
        CreatedBy      VARCHAR(20),
        CreatedOn      DATE
    )


    -- create table to temporarily hold TenderCategory filter
    DECLARE @CategoryTable TABLE
                           (
                               TenderCategory VARCHAR(100)
                           );

    IF @TenderCategory IS NOT NULL
        BEGIN
            INSERT INTO @CategoryTable (TenderCategory)
            SELECT TRIM(value)
            FROM STRING_SPLIT(@TenderCategory, ',');
        END
    INSERT INTO #tendercategories
    SELECT T.CategoryCode,
           T.TenderCategory,
           T.Description,
           U.NAME as CreatedBy,
           T.CreatedOn

    FROM t_TenderCategories T
             JOIN t_users U ON U.ID = T.CreatedBy

    WHERE (@FromDate IS NULL OR T.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR T.CreatedOn < DATEADD(DAY, 1, @ToDate))

      AND (
        @TenderCategory IS NULL
            OR EXISTS (SELECT 1
                       FROM @CategoryTable C
                       WHERE C.TenderCategory = T.TenderCategory)
        );


    Select * from #tendercategories

END

--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_tendercategories");
    }
};
