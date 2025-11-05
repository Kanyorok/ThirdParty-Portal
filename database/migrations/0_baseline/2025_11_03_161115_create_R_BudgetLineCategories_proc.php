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
        DB::unprepared("CREATE   PROC [dbo].[r_BudgetLineCategories] @FromDate SMALLDATETIME=NULL,
                                                    @ToDate SMALLDATETIME= NULL,
                                                    @IsActive VARCHAR(50)= NULL
AS
BEGIN

    CREATE TABLE #BudgetLineCategories
    (
        CategoryCode VARCHAR(100),
        CategoryName VARCHAR(100),
        Description  VARCHAR(300),
        Active       VARCHAR(50),
        CreatedBy    VARCHAR(100),
        CreatedOn    DATE

    )
    -- Declare a table variable to hold Active filter
    DECLARE @IsActiveTable TABLE
                           (
                               IsActive VARCHAR(50)
                           );
    IF @IsActive IS NOT NULL
        BEGIN
            INSERT INTO @IsActiveTable (IsActive)
            SELECT TRIM(value)
            FROM STRING_SPLIT(@IsActive, ',');
        END


    INSERT INTO #BudgetLineCategories
    SELECT B.CategoryCode,
           B.CategoryName,
           B.Description,
           B.IsActive,
           U.Name as CreatedBy,
           B.CreatedOn

    From t_BudgetLineCategories B
             JOIN t_Users U ON U.ID = B.CreatedBy

    WHERE (@FromDate IS NULL OR B.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR B.CreatedOn < DATEADD(DAY, 1, @ToDate))

      AND (
        @IsActive IS NULL OR
        EXISTS (SELECT 1
                FROM @IsActiveTable T
                WHERE T.IsActive = B.IsActive)
        );


    SELECT * FROM #BudgetLineCategories
END

--GO
--EXEC R_BudgetLineCategories

--Select distinct IsActive FROM t_BudgetLineCategories


--SELECT * FROM t_BudgetLineCategories
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BudgetLineCategories");
    }
};
