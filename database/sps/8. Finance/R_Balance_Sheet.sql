CREATE OR ALTER PROCEDURE R_Balance_Sheet
(
    @REPORTDATE DATE,
    @BRANCHES   VARCHAR(100)
)
AS
BEGIN
    SET NOCOUNT ON;
 
    DECLARE @NetProfit DECIMAL(18,2);
 
    CREATE TABLE #BS
    (
        ORDERCOL INT,
        GLCODE VARCHAR(50),
        GLNAME VARCHAR(100),
        GLACCOUNTTYPE VARCHAR(50),
        BALANCE DECIMAL(18,2)
    );
 
 
    ;WITH BRANCH_LIST AS (
        SELECT LTRIM(RTRIM(value)) AS BranchName
        FROM STRING_SPLIT(@BRANCHES, ',')
    ),
    GL_ACCOUNTS AS (
        SELECT ID, GLCode, GLName, GLAccountTypeID
        FROM t_FinanceGLAccounts WITH (NOLOCK)
        WHERE GLAccountTypeID IN ('A','L','S','I','E')
    ),
    TRANS_SUM AS (
        SELECT
            A.GLCode,
            A.GLAccountTypeID,
            SUM(T.Amount) AS Balance
        FROM t_FinancialTransactions T WITH (NOLOCK)
        JOIN t_Branches B ON B.ID = T.BranchID
        JOIN BRANCH_LIST BL ON BL.BranchName = B.Name
        JOIN GL_ACCOUNTS A ON A.ID = T.GLAccountID
        WHERE T.TransactionDate <= @REPORTDATE
        GROUP BY A.GLCode, A.GLAccountTypeID
    )
    SELECT
        @NetProfit =
            SUM(CASE WHEN GLAccountTypeID = 'I' THEN ISNULL(Balance,0) ELSE 0 END)
          - SUM(CASE WHEN GLAccountTypeID = 'E' THEN ISNULL(Balance,0) ELSE 0 END)
    FROM TRANS_SUM;
 

    ;WITH BRANCH_LIST AS (
        SELECT LTRIM(RTRIM(value)) AS BranchName
        FROM STRING_SPLIT(@BRANCHES, ',')
    ),
    GL_ACCOUNTS AS (
        SELECT ID, GLCode, GLName, GLAccountTypeID
        FROM t_FinanceGLAccounts WITH (NOLOCK)
        WHERE GLAccountTypeID IN ('A','L','S','I','E')
    ),
    TRANS_SUM AS (
        SELECT
            A.GLCode,
            A.GLAccountTypeID,
            SUM(T.Amount) AS Balance
        FROM t_FinancialTransactions T WITH (NOLOCK)
        JOIN t_Branches B ON B.ID = T.BranchID
        JOIN BRANCH_LIST BL ON BL.BranchName = B.Name
        JOIN GL_ACCOUNTS A ON A.ID = T.GLAccountID
        WHERE T.TransactionDate <= @REPORTDATE
        GROUP BY A.GLCode, A.GLAccountTypeID
    )
    INSERT INTO #BS
    SELECT
        CASE 
            WHEN A.GLAccountTypeID = 'A' THEN 1
            WHEN A.GLAccountTypeID = 'L' THEN 3
            WHEN A.GLAccountTypeID = 'S' THEN 5
        END,
        A.GLCode,
        A.GLName,
        A.GLAccountTypeID,
        ABS(ISNULL(T.Balance,0))
    FROM GL_ACCOUNTS A
    LEFT JOIN TRANS_SUM T ON A.GLCode = T.GLCode
    WHERE A.GLAccountTypeID IN ('A','L','S');
 

    INSERT INTO #BS
    VALUES (6,'','NET PROFIT / LOSS','S',@NetProfit);
 
    
    INSERT INTO #BS VALUES
    (2,'','','TOTAL ASSETS',
        (SELECT SUM(BALANCE) FROM #BS WHERE GLACCOUNTTYPE='A')),
    (4,'','','TOTAL LIABILITIES',
        (SELECT SUM(BALANCE) FROM #BS WHERE GLACCOUNTTYPE='L')),
    (7,'','','TOTAL EQUITY',
        (SELECT SUM(BALANCE) FROM #BS WHERE GLACCOUNTTYPE='S'));
 
    SELECT *
    FROM #BS
    ORDER BY ORDERCOL, GLCODE;
END;

