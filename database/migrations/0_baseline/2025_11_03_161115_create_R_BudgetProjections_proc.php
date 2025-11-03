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
        DB::unprepared("CREATE   PROC [dbo].[r_BudgetProjections] @FromDate smalldatetime=null,
                                                 @ToDate smalldatetime=null
AS
BEGIN

    CREATE TABLE #R_BudgetProjections
    (
        Budget    VARCHAR(300),
        Currency  VARCHAR(200),
        CreatedBy VARCHAR(100),
        CreatedOn DATE
    )

    INSERT INTO #R_BudgetProjections
    SELECT B.Name as BudgetID,
           C.Code as CurrencyID,
           U.Name as CreatedBy,
           P.CreatedOn

    FROM t_BudgetDriverProjections P
             JOIN t_budgets B ON B.ID = P.BudgetID
             JOIN t_users U ON U.ID = P.CreatedBy
             JOIN t_Currencies C ON C.ID = P.CurrencyID

    WHERE (@FromDate IS NULL OR P.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR P.CreatedOn < DATEADD(DAY, 1, @ToDate))

    SELECT * FROM #R_BudgetProjections
END
--GO
--EXEC R_BudgetProjections


--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BudgetProjections");
    }
};
