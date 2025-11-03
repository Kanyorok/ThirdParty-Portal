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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_PropertyMaintenanceAssignments](
    @FromDate Date,
    @ToDate Date
)
AS
BEGIN
    SET NOCOUNT ON

    CREATE TABLE #propertymaintenanceassignments
    (
        RequestNumber      NVarchar(200),
        AssignmentType     Varchar(200),
        ExpectedStartDate  Date,
        ExpectedCompletion Date,
        PriorityLevel      Varchar(200)
    )

    INSERT INTO #propertymaintenanceassignments
    (RequestNumber,
     AssignmentType,
     ExpectedStartDate,
     ExpectedCompletion,
     PriorityLevel)
    SELECT MN.RequestNumber      AS [Request Number],
           CD.Description        AS [Assignment Type],
           MA.ExpectedStartDate  AS [Expected Start Date],
           MA.ExpectedCompletion AS [Expected Completion],
           C.Description         AS [Priority Level]

    FROM t_AssignRequest as MA
             JOIN t_MaintenanceRequest as MN ON MA.RequestNumber = MN.Id
             JOIN t_Codedetails AS CD ON MA.AssignmentType = CD.ID AND CD.CodeID = 'AssignmentType'
             JOIN t_Codedetails AS C ON MA.PriorityLevel = C.ID AND C.CodeID = 'PriorityLevel'

    ORDER BY MN.RequestNumber

    SELECT * FROM #propertymaintenanceassignments

    DROP TABLE #propertymaintenanceassignments

    SET NOCOUNT OFF
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_propertymaintenanceassignments");
    }
};
