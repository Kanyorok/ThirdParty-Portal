CREATE OR ALTER PROCEDURE [dbo].[r_GLAccounts]
AS
BEGIN
    CREATE TABLE #GLAccounts
    (
        AccountID   BIGINT,
        Description VARCHAR(500),
        Currency    VARCHAR(100),
        CreatedBy   VARCHAR(100),
        CreatedOn   date
    )

    INSERT INTO #GLAccounts
    SELECT B.AccountID,
           B.Description,
           --C.Code AS Currency,
           b.CurrencyID,
           U.Name AS CreatedBy,
           B.CreatedOn
    FROM t_BudgetGLMaster B
             JOIN t_users U ON U.Id = B.CreatedBy
    --JOIN t_currencies C ON C.Id = B.CurrencyID

    SELECT * FROM #GLAccounts order by AccountID
END
--GO
--exec R_GLAccounts


--select * from t_Currencies c join t_BudgetGLMaster bm on bm.CurrencyID=c.Id

--select * from t_BudgetGLMaster
GO
