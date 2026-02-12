CREATE OR ALTER PROCEDURE R_CHART_OF_ACCOUNTS
@BRANCHID VARCHAR(100)

AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #ACCOUNTS
    (
        RowOrder        INT,
        GLCODE          NVARCHAR(100),
        GLNAME          VARCHAR(100),
        GLACCOUNTYPE    VARCHAR(100),
        BRANCH          VARCHAR(100)
    );

    INSERT INTO #ACCOUNTS
    SELECT
        ROW_NUMBER() OVER (
            ORDER BY 
                CASE G.GLAccountTypeID
                    WHEN 'A' THEN 1   -- Assets
                    WHEN 'L' THEN 2   -- Liabilities
                    WHEN 'S' THEN 3   -- Equity
                    WHEN 'I' THEN 4   -- Income
                    WHEN 'E' THEN 5   -- Expense
                    ELSE 6
                END,
                G.GLCode
        ) AS RowOrder,
        G.GLCode,
        G.GLName,
        CASE 
            WHEN G.GLAccountTypeID = 'A' THEN 'Asset'
            WHEN G.GLAccountTypeID = 'I' THEN 'Income'
            WHEN G.GLAccountTypeID = 'E' THEN 'Expense'
            WHEN G.GLAccountTypeID = 'L' THEN 'Liabilities'
            WHEN G.GLAccountTypeID = 'S' THEN 'Equity'
            ELSE 'Other'
        END AS GLACCOUNTYPE,
        B.Name AS Branch
    FROM t_FinanceGLAccounts G
    LEFT JOIN t_Branches B ON G.BranchID = B.ID

		WHERE

      @BRANCHID IS NULL
        OR @BRANCHID = 'ALL'
        OR LTRIM(RTRIM(B.Name)) IN
           (SELECT LTRIM(RTRIM(value)) FROM STRING_SPLIT(@BRANCHID, ','));
    


    SELECT *
    FROM #ACCOUNTS
    ORDER BY RowOrder;
END



