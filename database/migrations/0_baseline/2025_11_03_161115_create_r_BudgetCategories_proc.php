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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_BudgetCategories]
AS
BEGIN
    CREATE TABLE #BudgetCategories
    (
        CategoryCode VARCHAR(200),
        CategoryName VARCHAR(200),
        Description  VARCHAR(200),
        IsActive     BIT,
        CreatedBy    VARCHAR(200),
        CreatedOn    DATETIME,
        ModifiedBy   VARCHAR(200),
        ModifiedOn   DATETIME
    )

    INSERT INTO #BudgetCategories
    (CategoryCode,
     CategoryName,
     Description,
     IsActive,
     CreatedBy,
     CreatedOn,
     ModifiedBy,
     ModifiedOn)
    SELECT BL.CategoryCode,
           BL.CategoryName,
           BL.Description,
           BL.IsActive,
           U.Name AS CreatedBy,
           BL.CreatedOn,
           U.Name AS ModifiedBy,
           BL.ModifiedOn
    FROM t_BudgetLineCategories AS BL
             JOIN t_Users U ON U.ID = BL.CreatedBy
    WHERE BL.IsActive = 1

    SELECT * FROM #BudgetCategories;
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_BudgetCategories");
    }
};
