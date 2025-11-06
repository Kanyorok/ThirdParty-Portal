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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_NewBudget]
AS
BEGIN
    CREATE TABLE #NewBudget
    (
        BudgetName VARCHAR(200),
        FiscalYear DATETIME,
        [From]     DATETIME,
        [To]       DATETIME,
        Status     VARCHAR(200),

        CreatedBy  VARCHAR(200)
    )

    INSERT INTO #NewBudget
    (BudgetName,
     FiscalYear,
     [From],
     [To],
     Status,
     CreatedBy)
    SELECT NW.Name as BudgetName,
           NW.FiscalYear,
           NW.[From],
           NW.[To],
           NW.Status,

           U.Name  as CreatedBy
    FROM t_Budgets AS NW
             JOIN t_Users U ON NW.Id = U.Id

    SELECT * FROM #NewBudget;
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_NewBudget");
    }
};
