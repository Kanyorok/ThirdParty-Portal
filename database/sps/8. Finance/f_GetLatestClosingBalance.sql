--created tess 20205-oct-20
CREATE or ALTER FUNCTION f_GetLatestClosingBalance(
    @GLAccountID INT,
    @BranchID INT,
    @TransactionDate DATE
)
    RETURNS DECIMAL(18, 2)
AS
BEGIN
    DECLARE @ClosingBalance DECIMAL(18, 2);

    SELECT TOP 1 @ClosingBalance = ClosingBalance
    FROM t_GLClosingBalances
    WHERE GLAccountID = @GLAccountID
      AND BranchID = @BranchID
      AND BalanceDate < @TransactionDate
    ORDER BY BalanceDate DESC;

    RETURN ISNULL(@ClosingBalance, 0);
END;
