CREATE OR ALTER PROCEDURE R_Trial_Balance
(
    @FROMDATE DATE,
    @TODATE   DATE,
    @BRANCHES VARCHAR(100)
)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @NetProfit DECIMAL(18,2);

 --Get the list of branches
    ;WITH BRANCH_LIST AS (
        SELECT LTRIM(RTRIM(value)) AS BranchName
        FROM STRING_SPLIT(@BRANCHES, ',')
    ),
   --Get the list of GLAccounts
    GL_ACCOUNTS AS (
        SELECT ID, GLCode, GLName, GLAccountTypeID
        FROM t_FinanceGLAccounts WITH (NOLOCK)
        WHERE GLAccountTypeID IN ('A','L','S','I','E')
    ),
    ---Get Transactions summary
    TRANS_SUM AS (
        SELECT
            A.GLCode,
            A.GLName,
            A.GLAccountTypeID,
            SUM(T.Amount) AS Balance
        FROM t_FinancialTransactions T WITH (NOLOCK)
        JOIN t_Branches B ON B.ID = T.BranchID
        JOIN BRANCH_LIST BL ON BL.BranchName = B.Name
        JOIN GL_ACCOUNTS A ON A.ID = T.GLAccountID
        WHERE T.TransactionDate >= @FROMDATE
          AND T.TransactionDate <= @TODATE
        GROUP BY A.GLCode, A.GLName, A.GLAccountTypeID
    ),
--Get the Net profit
    NET_PROFIT AS (
        SELECT
            SUM(CASE WHEN GLAccountTypeID='I' THEN ISNULL(Balance,0) ELSE 0 END) -
            SUM(CASE WHEN GLAccountTypeID='E' THEN ISNULL(Balance,0) ELSE 0 END) AS Profit
        FROM TRANS_SUM
    ),

    TRIAL AS (
        SELECT
            GLCode,
            GLName,
            CASE WHEN GLAccountTypeID IN ('A','E') AND Balance >= 0 THEN Balance ELSE 0 END AS Debit,
            CASE WHEN GLAccountTypeID IN ('L','S','I') AND Balance >= 0 THEN Balance ELSE 0 END AS Credit,
            Balance
        FROM TRANS_SUM

        UNION ALL

        -- Net Profit / Loss Row
        SELECT
            '',
            'NET PROFIT / LOSS',
            CASE WHEN Profit >= 0 THEN Profit ELSE 0 END,
            CASE WHEN Profit < 0 THEN ABS(Profit) ELSE 0 END,
            Profit
        FROM NET_PROFIT
    )

    SELECT *
    FROM TRIAL
    ORDER BY GLCode;
END;
GO
