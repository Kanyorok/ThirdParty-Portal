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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_BudgetRateTypes]
AS
BEGIN
    CREATE TABLE #BudgetRateTypes
    (
        RatesCode   VARCHAR(200),
        RatesName   VARCHAR(200),
        Description VARCHAR(200)

    )

    INSERT INTO #BudgetRateTypes
    (RatesCode,
     RatesName,
     Description)
    SELECT RateTypeCode,
           RateTypeName,
           Description

    FROM t_BudgetRates


    SELECT * FROM #BudgetRateTypes;
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_BudgetRateTypes");
    }
};
