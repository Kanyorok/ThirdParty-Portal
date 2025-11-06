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
        DB::unprepared("CREATE   PROC [dbo].[r_ConsolidatedProcurementPlan] @FromDate SMALLDATETIME =NULL,
                                                           @ToDate SMALLDATETIME= NULL
AS
BEGIN
    CREATE TABLE #ConsolidatedProcurementPlan
    (

        Title           NVARCHAR(200),
        ReferenceNumber NVARCHAR(100),
        FiscalYear      BIGINT,
        --Status			VARCHAR(50),
        CreatedBy       VARCHAR(50),
        CreatedDate     smalldatetime
    )

    INSERT INTO #ConsolidatedProcurementPlan
    SELECT C.Title,
           C.ReferenceNumber,
           C.FiscalYear,
           --D.Description as Status,
           U.Name as CreatedBy,
           C.CreatedDate

    FROM t_ConsolidatedProcurementPlan C
             JOIN t_users U ON U.ID = C.CreatedBy
    --JOIN t_codedetails D ON D.CodeID =C.Status

    WHERE (@FromDate IS NULL OR C.CreatedDate >= @FromDate)
      AND (@ToDate IS NULL OR C.CreatedDate < DATEADD(DAY, 1, @ToDate))

    Select * from #ConsolidatedProcurementPlan

END

--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_ConsolidatedProcurementPlan");
    }
};
