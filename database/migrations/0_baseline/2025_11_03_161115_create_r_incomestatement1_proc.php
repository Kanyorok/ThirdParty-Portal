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
        DB::unprepared("CREATE   PROCEDURE r_incomestatement1
    @ReportDate DATETIME,
    @branchID NVARCHAR(200),
    @GLAccountType NVARCHAR(200) = NULL
AS
BEGIN

    CREATE TABLE #incomestatement
    (
        Branch NVARCHAR(200),
        AccountID NVARCHAR(200),
        GLName NVARCHAR(200),
        Description NVARCHAR(200),
        GLAccountType NVARCHAR(200),
        Balance MONEY,
        ReportDate DATE
    )

    INSERT INTO #incomestatement
    SELECT
        CAST(B.BranchID AS NVARCHAR(200)) + ' - ' + B.Name AS Branch,
        GL.GLCode AS AccountID,
        G.GLName AS GLName,
        G.Description AS Description,
        CASE G.GLAccountTypeID
            WHEN 'A' THEN 'Asset'
            WHEN 'I' THEN 'Income'
            WHEN 'E' THEN 'Expense'
            WHEN 'L' THEN 'Liability'
            WHEN 'S' THEN 'Shares'
            ELSE 'Unknown'
        END AS GLAccountType,
        CB.LocalBalance AS Balance,
        @ReportDate AS ReportDate
    FROM t_financeGLBranch GL
        JOIN t_branches B ON B.ID = GL.BranchID
        JOIN t_FinanceGLAccounts G ON G.ID = GL.GLAccountID
        JOIN t_GLClosingBalances CB ON CB.Id = GL.GLAccountID
    WHERE
        (
            @ReportDate IS NULL
            OR (
                GL.CreatedOn >= DATEFROMPARTS(YEAR(@ReportDate), 1, 1)
                AND GL.CreatedOn < DATEADD(DAY, 1, @ReportDate)
            )
        )
        AND (
            @branchID IS NULL
            OR @branchID = 'ALL'
            OR B.Name IN (SELECT value FROM STRING_SPLIT(@branchID, ','))
        )
        AND (
            @GLAccountType IS NULL
            OR @GLAccountType = 'ALL'
            OR CASE G.GLAccountTypeID
                WHEN 'A' THEN 'Asset'
                WHEN 'I' THEN 'Income'
                WHEN 'E' THEN 'Expense'
                WHEN 'L' THEN 'Liability'
                WHEN 'S' THEN 'Shares'
                ELSE 'Unknown'
              END IN (SELECT TRIM(value) FROM STRING_SPLIT(@GLAccountType, ','))
        );

    SELECT * FROM #incomestatement;

END


");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_incomestatement1");
    }
};
