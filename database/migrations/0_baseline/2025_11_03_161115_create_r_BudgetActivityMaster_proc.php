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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_BudgetActivityMaster]
AS
BEGIN
    CREATE TABLE #BudgetActivityMaster
    (
        ActivityName VARCHAR(200),
        BudgetLine   VARCHAR(200),
        Description  VARCHAR(200)

    )

    INSERT INTO #BudgetActivityMaster
    (ActivityName,
     BudgetLine,
     Description)
    SELECT BM.ActivityName,
           BL.LineName,
           BM.Description

    FROM t_BudgetActivityMaster AS BM
             JOIN t_BudgetLines BL ON BM.Id = BL.Id


    SELECT * FROM #BudgetActivityMaster;
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_BudgetActivityMaster");
    }
};
