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
        DB::unprepared("CREATE   PROC [dbo].[r_TenderInitiation] @FromDate smalldatetime=null,
                                                @ToDate smalldatetime = null,
                                                @TenderCategory varchar(20) = null
AS
BEGIN
    CREATE TABLE #TenderInitiation
    (
        TenderNo           VARCHAR(20),
        Title              VARCHAR(100),
        --TenderType VARCHAR(20)
        TenderCategory     VARCHAR(20),
        ScopeOfWork        VARCHAR(500),
        Instructions       VARCHAR(500),
        SubmissionDeadline DATE,
        OpeningDate        DATE,
        --Status VARCHAR(20)
        CreatedBy          VARCHAR(20),
        CreatedOn          DATE,
        CurrencyId         VARCHAR(200),
        ApprovalRemarks    VARCHAR(100)
        --ApprovalStatus VARCHAR (20)
    )


    -- create table to temporarily hold TenderCategory filter
    DECLARE @CategoryTable TABLE
                           (
                               TenderCategory VARCHAR(100)
                           );

    IF @TenderCategory IS NOT NULL
        BEGIN
            INSERT INTO @CategoryTable (TenderCategory)
            SELECT TRIM(value)
            FROM STRING_SPLIT(@TenderCategory, ',');
        END
    INSERT INTO #TenderInitiation (TenderNo,
                                   Title,
        --TenderType
                                   TenderCategory,
                                   ScopeOfWork,
                                   Instructions,
                                   SubmissionDeadline,
                                   OpeningDate,
        --Status
                                   CreatedBy,
                                   CreatedOn,
                                   CurrencyId,
                                   ApprovalRemarks
        --ApprovalStatus
    )
    SELECT T.TenderNo,
           T.Title,
           --T.TenderType
           R.TenderCategory as TenderCategory,
           T.ScopeOfWork,
           T.Instructions,
           T.SubmissionDeadline,
           T.OpeningDate,
           --T.Status
           U.Name           as CreatedBy,
           T.CreatedOn,
           C.Name           as CurrencyId,
           T.ApprovalRemarks
    --T.ApprovalStatus
    FROM t_Tenders T
             JOIN t_Users U ON U.Id = T.CreatedBy
             JOIN t_currencies C ON C.Id = T.CurrencyId
             JOIN t_tendercategories R ON R.Id = T.TenderCategory

    WHERE (@FromDate IS NULL OR T.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR T.CreatedOn < DATEADD(DAY, 1, @ToDate))
      AND (
        @TenderCategory IS NULL
            OR EXISTS (SELECT 1
                       FROM @CategoryTable C
                       WHERE C.TenderCategory = R.TenderCategory)
        );

    SELECT * FROM #TenderInitiation;
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_TenderInitiation");
    }
};
