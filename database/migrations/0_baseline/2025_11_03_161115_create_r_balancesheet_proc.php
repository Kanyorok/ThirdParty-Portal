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
        DB::unprepared("CREATE   PROCEDURE r_balancesheet
    @ReportDate DATETIME,
    @BranchID NVARCHAR(200)
AS
BEGIN
    SET NOCOUNT ON;

    -- Temporary table for storing the balance sheet data
    CREATE TABLE #balancesheet
    (
        Branch          NVARCHAR(200),
        AccountID       NVARCHAR(200),
        GLName          NVARCHAR(200),
        Description     NVARCHAR(200),
        GLAccountType   NVARCHAR(200),
        Balance         MONEY,
        ReportDate      DATE
    );

   
    INSERT INTO #balancesheet
    SELECT
        CAST(B.BranchID AS NVARCHAR(200)) + ' - ' + B.Name AS Branch,
        GL.GLCode AS AccountID,
        G.GLName,
        G.Description,
        CASE GL.GLAccountType
            WHEN 'A' THEN 'Asset'
            WHEN 'I' THEN 'Income'
            WHEN 'E' THEN 'Expense'
            WHEN 'L' THEN 'Liability'
            WHEN 'S' THEN 'Shares'
            ELSE 'Unknown'
        END AS GLAccountType,
        CB.LocalBalance AS Balance,
        @ReportDate AS ReportDate
    FROM 
        t_financeGLBranch GL
        INNER JOIN t_branches B ON B.ID = GL.BranchID
        INNER JOIN t_FinanceGLAccounts G ON G.ID = GL.GLAccountID
        INNER JOIN t_GLClosingBalances CB ON CB.GLAccountID = GL.GLAccountID  -- ✅ Correct join key
    WHERE 
        (@ReportDate IS NULL OR GL.CreatedOn <= @ReportDate)
        AND GL.GLAccountType IN ('A', 'L') 
        AND (
            @BranchID IS NULL OR
            @BranchID = 'ALL' OR 
            B.Name IN (SELECT value FROM string_split(@BranchID, ','))
        );

    
    SELECT *
    FROM #balancesheet
    ORDER BY GLAccountType, Branch, AccountID;
END;
--GO 
--	EXEC  r_balancesheet

--	@Reportdate ='2025-10-23',

--	@branchID = 'Head Office'


");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_balancesheet");
    }
};
