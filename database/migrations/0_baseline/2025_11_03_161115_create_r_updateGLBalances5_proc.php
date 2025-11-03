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
        DB::unprepared("CREATE      PROCEDURE r_updateGLBalances5     
AS      
BEGIN      
    SET NOCOUNT ON;      
      
    -- Update existing records      
    UPDATE CB      
    SET      
        CB.GLAccountID = FL.GLAccountID,      
        CB.BranchID = FL.BranchID,    
        CB.BalanceDate = FL.TransactionDate,        
        CB.OpeningBalance = dbo.fn_GetLatestClosingBalance(FL.GLAccountID,FL.BranchID,FL.TransactionDate),    
        CB.ClosingBalance = FL.Amount,      
        CB.LocalBalance = FL.Amount,      
        CB.ForeignBalance = FL.Amount,      
        CB.CreatedBy = FL.CreatedBy,      
        CB.CreatedOn = FL.CreatedOn,      
        CB.ModifiedBy = FL.ModifiedBy,      
        CB.ModifiedOn = FL.ModifiedOn,      
        CB.DeletedBy = FL.DeletedBy,      
        CB.DeletedOn = FL.DeletedOn      
    FROM t_GLClosingBalances CB      
    INNER JOIN t_FinancialTransactions FL     
        ON FL.GLAccountID = CB.GLAccountID     
        AND FL.BranchID = CB.BranchID    
        AND CAST(FL.TransactionDate AS DATE) = CAST(CB.BalanceDate AS DATE)    
    LEFT JOIN t_GLClosingBalances PrevCB    
        ON CB.GLAccountID = FL.GLAccountID    
        AND CB.BranchID = FL.BranchID    
      AND CAST(FL.TransactionDate AS DATE)=CAST(CB.BalanceDate as DATE)    
      
    -- Insert new records only if they don't already exist for the date      
    INSERT INTO t_GLClosingBalances (      
        GLAccountID,      
        BranchID,    
        BalanceDate,       
        OpeningBalance,      
        ClosingBalance,      
        LocalBalance,      
        ForeignBalance,      
        CreatedBy,      
        CreatedOn,      
        ModifiedBy,      
        ModifiedOn,      
        DeletedBy,      
        DeletedOn      
    )      
    SELECT      
        FL.GLAccountID,      
        FL.BranchID,    
        CAST(FL.TransactionDate AS DATE) AS BalanceDate,    
        dbo.fn_GetLatestClosingBalance(FL.GLAccountID,FL.BranchID,FL.TransactionDate),    
        FL.Amount,      
        FL.Amount,      
        FL.Amount,      
        FL.CreatedBy,      
        FL.CreatedOn,      
        FL.ModifiedBy,      
        FL.ModifiedOn,    
        FL.DeletedBy,      
        FL.DeletedOn     
    FROM t_FinancialTransactions FL      
    LEFT JOIN t_GLClosingBalances CB     
        ON FL.GLAccountID = CB.GLAccountID      
        AND FL.BranchID = CB.BranchID    
        AND CAST(FL.TransactionDate AS DATE) = CAST(CB.BalanceDate AS DATE)      
    LEFT JOIN t_GLClosingBalances PrevCB    
        ON CB.GLAccountID = FL.GLAccountID    
        AND CB.BranchID = FL.BranchID    
        AND CAST(FL.TransactionDate AS DATE)=CAST(CB.BalanceDate as DATE)    
    WHERE CB.GLAccountID IS NULL;      
END; 
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_updateGLBalances5");
    }
};
