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
        DB::unprepared("CREATE   PROC [dbo].[r_BidSubmissions] @FromDate SMALLDATETIME=NULL,
                                              @ToDate SMALLDATETIME=NULL,
                                              @SubmissionMode VARCHAR(200) =NULL
AS
BEGIN
    CREATE TABLE #BidSubmissions
    (
        TenderRef      VARCHAR(200),
        SupplierName   VARCHAR(200),
        SubmissionMode VARCHAR(200),
        ReceivedAt     DATE,
        Remarks        VARCHAR(1000),
        CreatedBy      VARCHAR(100),
        CreatedOn      DATE
    )


    INSERT INTO #BidSubmissions
    SELECT B.TenderRef,
           B.SupplierName,
           C.Description as SubmissionMode,
           B.ReceivedAt,
           B.Remarks,
           U.Name        as CreatedBy,
           B.CreatedOn


    FROM t_BidSubmissions B
             JOIN t_users U ON U.Id = B.CreatedBy
             JOIN t_CodeDetails C ON C.ID = B.SubmissionMode
    Where (@FromDate IS NULL OR B.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR B.CreatedOn < DATEADD(DAY, 1, @ToDate))

      AND (
        @SubmissionMode IS NULL OR
        @SubmissionMode = 'ALL' OR
        C.Description IN (SELECT value FROM STRING_SPLIT(@SubmissionMode, ','))
        );

    SELECT * FROM #BidSubmissions;

END
--GO
--Exec r_BidSubmissions
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_BidSubmissions");
    }
};
