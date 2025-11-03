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
        DB::unprepared("CREATE    PROC [dbo].[r_ItemCategories] 

			@FromDate DATE = Null,
            @ToDate Date = null,
			@ParentCategory NVARCHAR(200) = NULL
AS

BEGIN
    CREATE TABLE #ItemCategories
    (

        --Parent_Category         NVARCHAR(200),
        Description  VARCHAR(200),
		Sub_Category NVARCHAR(200),
		CategoryCode NVARCHAR(200),
        CreatedOn    DATE,
        CreatedBy    VARCHAR(100)

    )

    INSERT INTO #ItemCategories

    SELECT 
	       --I.Name AS Parent_Category,
           I.Description,
		   S.Name AS Sub_Category,
           I.CategoryCode,
           I.CreatedOn,
           U.Name

    FROM t_ItemCategories I
             JOIN t_Users U ON U.ID = I.CreatedBy
			 LEFT JOIN t_ItemCategories S ON S.ParentId = I.ID 
    WHERE (@FromDate IS NULL OR I.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR I.CreatedOn < DATEADD(DAY, 1, @ToDate))
	  AND S.ParentId IS NOT NULL;

    SELECT * FROM #ItemCategories

END

--GO

--EXEC  r_ItemCategories
--@FromDate = '1 jan 2025',
--@ToDate  = '21 Aug 2025'


--GO


");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_ItemCategories");
    }
};
