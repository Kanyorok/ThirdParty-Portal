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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_PropertyMaintenanceRequirements](
    @FromDate smallDatetime,
    @ToDate smallDatetime
)
AS
BEGIN
    CREATE TABLE #MaintenanceRequirements
    (

        RequestNumber VARCHAR(200),
        Property      VARCHAR(200),
        ReportedBy    VARCHAR(200),
        CreatedOn     smallDatetime,
        IssueType     VARCHAR(200),
        Priority      VARCHAR(200)

    )

    INSERT INTO #MaintenanceRequirements (RequestNumber,
                                          Property,
                                          ReportedBy,
                                          CreatedOn,
                                          IssueType,
                                          Priority)
    SELECT MN.RequestNumber,
           PR.PropertyName AS [Property],
           MN.ReportedBy   AS [Reported By],
           MN.CreatedOn,
           CD.Description  AS [Issue Type],
           C.Description   AS [Priority]
    FROM t_MaintenanceRequest AS MN
             JOIN t_PropertyRegistry PR ON MN.Property = PR.Id
             JOIN t_CodeDetails C ON MN.Priority = C.ID AND C.CodeID = 'PriorityLevel'
             LEFT JOIN t_CodeDetails CD ON MN.IssueType = CD.ID AND CD.CodeID = 'IssueType'

    WHERE MN.CreatedOn BETWEEN @FromDate AND @ToDate + 1
    ORDER BY MN.RequestNumber

    SELECT * FROM #MaintenanceRequirements;

    DROP TABLE #MaintenanceRequirements;
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_propertymaintenancerequirements");
    }
};
