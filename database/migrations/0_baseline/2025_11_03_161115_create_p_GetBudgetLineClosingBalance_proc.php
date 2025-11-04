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
        DB::unprepared("CREATE   PROCEDURE [dbo].[p_GetBudgetLineClosingBalance]
    @BudgetLineID BIGINT,
    @BranchID     NVARCHAR(10),
    @AsOnDate     DATE,
    @LocalOrForeign CHAR(1) = 'L'  -- L=Local, F=Foreign
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @AccountID NVARCHAR(50);
    DECLARE @Balance DECIMAL(18,2);
    -- Get CBS AccountID mapped to this BudgetLine
    SELECT TOP 1 @AccountID = bg.AccountID
    FROM t_BudgetLinesGLAccounts bgl,t_BudgetGLMaster bg
    WHERE 
	bg.BudgetGLID=bgl.BudgetGLAccountID and
	bgl.BudgetLineID = @BudgetLineID
      AND bgl.DeletedOn IS NULL;
    IF @AccountID IS NULL
    BEGIN
        RAISERROR('No CBS GL mapping found for this BudgetLine.', 16, 1);
        RETURN;
    END
    -- Call CBS function on linked server
    SELECT @Balance = BRNET_KCBL_TRAIN.dbo.f_GetClosingBalanceGLNow(
                        @BranchID,
                        @AccountID,
                        @AsOnDate,
                        @LocalOrForeign
                     );
    -- Return result
    SELECT @BudgetLineID AS BudgetLineID,
           @BranchID AS BranchID,
           @AccountID AS LedgerAccount,
           @AsOnDate AS AsOnDate,
           @Balance AS ClosingBalance;
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS p_GetBudgetLineClosingBalance");
    }
};
