CREATE OR ALTER PROCEDURE [dbo].[r_GLStatement]
(
    @FromDate   DATE = NULL,
    @ToDate     DATE = NULL,
    @BranchID   NVARCHAR(200) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    
    ;WITH JournalDaily AS
    (
        SELECT
            JL.GLAccountID,
            CAST(JL.CreatedOn AS DATE) AS TrxDate,
            SUM(ISNULL(JL.Debit,0)) AS Debit,
            SUM(ISNULL(JL.Credit,0)) AS Credit
        FROM t_FinanceJournalLines JL
        WHERE
            (@FromDate IS NULL OR JL.CreatedOn >= @FromDate)
            AND (@ToDate IS NULL OR JL.CreatedOn < DATEADD(DAY,1,@ToDate))
        GROUP BY
            JL.GLAccountID,
            CAST(JL.CreatedOn AS DATE)
    ),

    
    GLDaily AS
    (
        SELECT
            CAST(B.BranchID AS NVARCHAR(50)) + ' - ' + B.Name AS BranchID,
            GL.GLCode AS AccountID,    
            JD.TrxDate,
            JD.Debit,
            JD.Credit,
            GL.GLAccountID             
        FROM JournalDaily JD
            INNER JOIN t_FinanceGLBranch GL
                ON GL.GLAccountID = JD.GLAccountID
            INNER JOIN t_Branches B
                ON B.ID = GL.BranchID
        WHERE
            (@BranchID IS NULL OR @BranchID = 'ALL' 
             OR B.Name IN (SELECT TRIM(value) FROM STRING_SPLIT(@BranchID,',')))
    ),

   
    RunningBalance AS
    (
        SELECT
            GLD.BranchID,
            GLD.AccountID,
            A.GLName AS AccountName,       
            GLD.TrxDate,
            GLD.Debit,
            GLD.Credit,
            ISNULL(GC.OpeningBalance,0) AS StartingBalance,
            SUM(GLD.Debit - GLD.Credit) 
                OVER (PARTITION BY GLD.BranchID, GLD.GLAccountID ORDER BY GLD.TrxDate
                      ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS CumTrans,
            GLD.GLAccountID
        FROM GLDaily GLD
            LEFT JOIN t_FinanceGLAccounts A
                ON A.ID = GLD.GLAccountID     
            LEFT JOIN
            (
                SELECT GLAccountID, MAX(OpeningBalance) AS OpeningBalance
                FROM t_GLClosingBalances
                GROUP BY GLAccountID
            ) GC
                ON GC.GLAccountID = GLD.GLAccountID
    )

    
    SELECT
        BranchID,
        AccountID,
        AccountName,
        TrxDate,
        StartingBalance + ISNULL(LAG(CumTrans,1) OVER (PARTITION BY BranchID, GLAccountID ORDER BY TrxDate),0) AS OpeningBalance,
        Debit,
        Credit,
        StartingBalance + CumTrans AS ClosingBalance
    FROM RunningBalance
    ORDER BY BranchID, AccountID, TrxDate;

END;

