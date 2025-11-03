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
        DB::unprepared("CREATE   PROCEDURE dbo.p_SyncLedgerLimitsFromBudget
    @BudgetID BIGINT,
    @UserID   BIGINT
AS
BEGIN
    SET NOCOUNT ON;
 
    BEGIN TRY
        BEGIN TRAN;
 
        ---------------------------------------------------------------------
        -- Get Budget year and sanity dates
        ---------------------------------------------------------------------
        DECLARE @FiscalYear INT, @FromDate DATE, @ToDate DATE;
        SELECT
            @FiscalYear = b.FiscalYear,
            @FromDate   = b.[From],
            @ToDate     = b.[To]
        FROM dbo.t_Budgets b
        WHERE b.Id = @BudgetID;
 
        IF @FiscalYear IS NULL
        BEGIN
            RAISERROR('Budget %d not found.', 16, 1, @BudgetID);
            ROLLBACK TRAN;
            RETURN;
        END;
 
        ---------------------------------------------------------------------
        -- TEMP: Activity-driven monthly limits
        ---------------------------------------------------------------------
        IF OBJECT_ID('tempdb..#ActMonthly') IS NOT NULL DROP TABLE #ActMonthly;
        CREATE TABLE #ActMonthly
        (
            BudgetLineID BIGINT NOT NULL,
            BranchID     BIGINT NOT NULL,
            MonthNo      TINYINT NOT NULL,
            LedgerID     NVARCHAR(50) NOT NULL,   -- CBS GL ID
            ERPLedgerID  NVARCHAR(50) NOT NULL,   -- ERP GL Mapping ID
            LimitAmount  DECIMAL(15,2) NOT NULL
        );
 
        INSERT INTO #ActMonthly (BudgetLineID, BranchID, MonthNo, LedgerID, ERPLedgerID, LimitAmount)
        SELECT
            ba.BudgetLineID,
            ba.BranchID,
            ma.[Month] AS MonthNo,
            bm.AccountID AS LedgerID, -- CBS
            CAST(blga.BudgetGLAccountID AS NVARCHAR(50)) AS ERPLedgerID, -- ERP
            CAST(SUM(ma.Amount) AS DECIMAL(15,2)) AS LimitAmount
        FROM dbo.t_BudgetMonthlyAllocations ma
        INNER JOIN dbo.t_BudgetActivities ba
            ON ma.BudgetActivityID = ba.Id
        INNER JOIN dbo.t_BudgetLinesGLAccounts blga
            ON ba.BudgetLineID = blga.BudgetLineID
        INNER JOIN dbo.t_BudgetGLMaster bm
            ON blga.BudgetGLAccountID = bm.BudgetGLID
        WHERE ba.BudgetID = @BudgetID
        GROUP BY ba.BudgetLineID, ba.BranchID, ma.[Month], blga.BudgetGLAccountID, bm.AccountID;
 
        ---------------------------------------------------------------------
        -- TEMP: Manual-driven monthly limits
        ---------------------------------------------------------------------
        IF OBJECT_ID('tempdb..#ManMonthly') IS NOT NULL DROP TABLE #ManMonthly;
        CREATE TABLE #ManMonthly
        (
            BudgetLineID BIGINT NOT NULL,
            BranchID     BIGINT NOT NULL,
            MonthNo      TINYINT NOT NULL,
            LedgerID     NVARCHAR(50) NOT NULL,
            ERPLedgerID  NVARCHAR(50) NOT NULL,
            LimitAmount  DECIMAL(15,2) NOT NULL
        );
 
        INSERT INTO #ManMonthly (BudgetLineID, BranchID, MonthNo, LedgerID, ERPLedgerID, LimitAmount)
        SELECT
            me.BudgetLineID,
            me.BranchID,
            TRY_CONVERT(TINYINT, mea.[Month]) AS MonthNo,
            bm.AccountID AS LedgerID,
            CAST(blga.BudgetGLAccountID AS NVARCHAR(50)) AS ERPLedgerID,
            CAST(SUM(mea.Allocation) AS DECIMAL(15,2)) AS LimitAmount
        FROM dbo.t_BudgetManualEntryAllocations mea
        INNER JOIN dbo.t_BudgetManualEntry me
            ON mea.EntryID = me.Id
           AND mea.BudgetID = me.BudgetID
        INNER JOIN dbo.t_BudgetLinesGLAccounts blga
            ON me.BudgetLineID = blga.BudgetLineID
        INNER JOIN dbo.t_BudgetGLMaster bm 
            ON blga.BudgetGLAccountID = bm.BudgetGLID
        WHERE me.BudgetID = @BudgetID
          AND TRY_CONVERT(TINYINT, mea.[Month]) BETWEEN 1 AND 12
        GROUP BY me.BudgetLineID, me.BranchID, TRY_CONVERT(TINYINT, mea.[Month]), bm.AccountID, blga.BudgetGLAccountID;
 
        ---------------------------------------------------------------------
        -- Combine activity + manual into #AllMonthly with Effective Dates
        ---------------------------------------------------------------------
        IF OBJECT_ID('tempdb..#AllMonthly') IS NOT NULL DROP TABLE #AllMonthly;
        CREATE TABLE #AllMonthly
        (
            BudgetLineID BIGINT NOT NULL,
            BranchID     BIGINT NOT NULL,
            MonthNo      TINYINT NOT NULL,
            LedgerID     NVARCHAR(50) NOT NULL,
            ERPLedgerID  NVARCHAR(50) NOT NULL,
            LimitAmount  DECIMAL(15,2) NOT NULL,
            EffectiveFrom DATE NOT NULL,
            EffectiveTo   DATE NOT NULL
        );
 
        -- Activity-driven rows
        INSERT INTO #AllMonthly (BudgetLineID, BranchID, MonthNo, LedgerID, ERPLedgerID, LimitAmount, EffectiveFrom, EffectiveTo)
        SELECT
            a.BudgetLineID,
            a.BranchID,
            a.MonthNo,
            a.LedgerID,
            a.ERPLedgerID,
            a.LimitAmount,
            DATEFROMPARTS(@FiscalYear, a.MonthNo, 1) AS EffectiveFrom,
            EOMONTH(DATEFROMPARTS(@FiscalYear, a.MonthNo, 1)) AS EffectiveTo
        FROM #ActMonthly a;
 
        -- Manual-driven rows
        INSERT INTO #AllMonthly (BudgetLineID, BranchID, MonthNo, LedgerID, ERPLedgerID, LimitAmount, EffectiveFrom, EffectiveTo)
        SELECT
            m.BudgetLineID,
            m.BranchID,
            m.MonthNo,
            m.LedgerID,
            m.ERPLedgerID,
            m.LimitAmount,
            DATEFROMPARTS(@FiscalYear, m.MonthNo, 1) AS EffectiveFrom,
            EOMONTH(DATEFROMPARTS(@FiscalYear, m.MonthNo, 1)) AS EffectiveTo
        FROM #ManMonthly m;
 
        ---------------------------------------------------------------------
        -- Insert only NEW rows into t_BudgetLineLedgerLimits
        ---------------------------------------------------------------------
        INSERT INTO dbo.t_BudgetLineLedgerLimits
        (
            BudgetID,
            BudgetLineID,
            LedgerID,
            ERPLedgerID,
            BranchID,
            LimitType,
            LimitAmount,
            EffectiveFrom,
            EffectiveTo,
            IsActive,
            CreatedBy,
            CreatedOn
        )
        SELECT
            @BudgetID,
            s.BudgetLineID,
            s.LedgerID,
            s.ERPLedgerID,
            s.BranchID,
            'Monthly' AS LimitType,
            s.LimitAmount,
            s.EffectiveFrom,
            s.EffectiveTo,
            1 AS IsActive,
            @UserID,
            GETDATE()
        FROM #AllMonthly s
        WHERE NOT EXISTS
        (
            SELECT 1
            FROM dbo.t_BudgetLineLedgerLimits l
            WHERE l.BudgetID     = @BudgetID
              AND l.BudgetLineID  = s.BudgetLineID
              AND l.LedgerID      = s.LedgerID
              AND l.ERPLedgerID   = s.ERPLedgerID
              AND l.BranchID      = s.BranchID
              AND l.EffectiveFrom = s.EffectiveFrom
              AND l.IsActive      = 1
        );
 
        COMMIT TRAN;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK TRAN;
 
        DECLARE @ErrMsg NVARCHAR(4000) = ERROR_MESSAGE(),
                @ErrSev INT = ERROR_SEVERITY(),
                @ErrSt  INT = ERROR_STATE();
        RAISERROR(@ErrMsg, @ErrSev, @ErrSt);
        RETURN;
    END CATCH
END;

--GO
--BEGIN TRAN
--EXEC p_SyncLedgerLimitsFromBudget 5,1
--select * from t_BudgetLineLedgerLimits
--ROLLBACK TRAN
 
 
 
 
 
 
 
 
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS p_SyncLedgerLimitsFromBudget");
    }
};
