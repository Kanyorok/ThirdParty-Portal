--created tess 20205-oct-20
CREATE OR ALTER PROCEDURE p_updateGLBalances
AS
BEGIN
    SET NOCOUNT ON;

    -- Update existing records
    UPDATE CB
    SET CB.GLAccountID    = FL.GLAccountID,
        CB.BranchID       = FL.BranchID,
        CB.BalanceDate    = FL.TransactionDate,
        CB.OpeningBalance =(select dbo.f_GetLatestClosingBalance(FL.GLAccountID, FL.BranchID, FL.TransactionDate)),
        CB.ClosingBalance = FL.Amount,
        CB.LocalBalance   = FL.Amount,
        CB.ForeignBalance = FL.Amount,
        CB.CreatedBy      = FL.CreatedBy,
        CB.CreatedOn      = FL.CreatedOn,
        CB.ModifiedBy     = FL.ModifiedBy,
        CB.ModifiedOn     = FL.ModifiedOn,
        CB.DeletedBy      = FL.DeletedBy,
        CB.DeletedOn      = FL.DeletedOn
    FROM t_GLClosingBalances CB
             INNER JOIN t_FinancialTransactions FL
                        ON FL.GLAccountID = CB.GLAccountID
                            AND FL.BranchID = CB.BranchID
                            AND CAST(FL.TransactionDate AS DATE) = CAST(CB.BalanceDate AS DATE)
             LEFT JOIN t_GLClosingBalances PrevCB
                       ON CB.GLAccountID = FL.GLAccountID
                           AND CB.BranchID = FL.BranchID
                           AND CAST(FL.TransactionDate AS DATE) = CAST(CB.BalanceDate as DATE)

    -- Insert new records only if they don't already exist for the date
    INSERT INTO t_GLClosingBalances (GLAccountID,
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
                                     DeletedOn)
    SELECT FL.GLAccountID,
           FL.BranchID,
           CAST(FL.TransactionDate AS DATE) AS BalanceDate,
           (select dbo.f_GetLatestClosingBalance(FL.GLAccountID, FL.BranchID, FL.TransactionDate)),
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
                           AND CAST(FL.TransactionDate AS DATE) = CAST(CB.BalanceDate as DATE)
    WHERE CB.GLAccountID IS NULL;
END;
