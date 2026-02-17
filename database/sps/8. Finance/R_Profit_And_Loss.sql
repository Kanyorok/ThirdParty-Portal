CREATE OR ALTER PROCEDURE R_Profit_And_Loss
(
    @REPORTDATE DATETIME,
    @BRANCHES   VARCHAR(100)
)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @FROMDATE DATETIME;
    SET @FROMDATE = DATEFROMPARTS(YEAR(@REPORTDATE), 1, 1);

    CREATE TABLE #PL
    (
        ORDERCOL  INT,
        GLCODE VARCHAR(50),
        GLNAME VARCHAR(100),
        GLACCOUNTTYPE VARCHAR(50),
        BALANCE DECIMAL(18,2)
    );

    ;WITH PL_ACCOUNTS AS (
        SELECT id, A.GLCode, GLName, GLAccountTypeID
        FROM t_FinanceGLAccounts A WITH (NOLOCK)
        WHERE GLAccountTypeID IN ('I','E')
    ),
    PL_TRANSUM AS (
        SELECT A.GLCode, SUM(ABS(Amount)) AS Balance
        FROM t_FinancialTransactions T WITH (NOLOCK)
		JOIN t_branches B ON B.ID=T.BranchID
        JOIN PL_ACCOUNTS A ON A.id = T.GLAccountID
		
        WHERE CAST(Transactiondate AS DATE)
              BETWEEN @FROMDATE AND @REPORTDATE
			  AND B.Name IN (
		 SELECT LTRIM(RTRIM(value))
			 FROM STRING_SPLIT(@BRANCHES, ',')
)
        GROUP BY A.GLCode
    ),
    PL_FINAL AS (
        SELECT
            CASE WHEN GLAccountTypeID='I' THEN 1
                 WHEN GLAccountTypeID='E' THEN 3 END AS OrderCol,
            S.GLCode, GLName, GLAccountTypeID, Balance
        FROM PL_ACCOUNTS S
        JOIN PL_TRANSUM R ON S.GLCode = R.GLCode
    )
    INSERT INTO #PL
    SELECT OrderCol, GLCode, GLName, GLAccountTypeID, Balance
    FROM PL_FINAL;

    INSERT INTO #PL
    VALUES
    (2,'','','TOTAL INCOME',(SELECT SUM(BALANCE) FROM #PL WHERE GLACCOUNTTYPE='I')),
    (4,'','','TOTAL EXPENSE',(SELECT SUM(BALANCE) FROM #PL WHERE GLACCOUNTTYPE='E')),
    (5,'','','PROFIT/LOSS',
        (SELECT SUM(BALANCE) FROM #PL WHERE GLACCOUNTTYPE='I')
      - (SELECT SUM(BALANCE) FROM #PL WHERE GLACCOUNTTYPE='E')
    );

    SELECT * FROM #PL ORDER BY ORDERCOL, GLCODE;
END


--exec R_Profit_And_Loss2

--    @REPORTDATE ='03 Jan 2026',
--    @BRANCHES   ='Head Office'

