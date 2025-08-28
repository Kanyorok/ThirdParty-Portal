CREATE OR ALTER PROC [dbo].[r_BudgetEntryListingtest] @FromDate smalldatetime = null,
                                                      @ToDate smalldatetime = null
AS
BEGIN

    CREATE TABLE #BudgetEntryListingtest
    (
        Budget      VARCHAR(200),
        Branch      VARCHAR(100),
        BudgetLine  VARCHAR(300),
        Amount      MONEY,
        Comments    VARCHAR(300),
        CreatedBy   VARCHAR(100),
        CreatedOn   DATE,
        TotalAmount DECIMAL(18, 2)
    )

    INSERT INTO #BudgetEntryListingtest
    SELECT B.Name      as BudgetID,
           BR.Name     as BranchID,
           BL.LineName as BudgetLineID,
           E.Amount,
           E.Comments,
           U.Name      as CreatedBy,
           E.CreatedOn,
           NUll        as TotalAmount

    FROM t_BudgetManualEntry E
             JOIN t_users U ON U.ID = E.CreatedBy
             JOIN t_Branches BR ON BR.ID = E.BranchID
             JOIN t_budgets B ON B.ID = E.BudgetID
             JOIN t_BudgetLines BL ON BL.ID = E.BudgetLineID

    WHERE (@FromDate IS NULL OR E.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR E.CreatedOn < DATEADD(DAY, 1, @ToDate))

    -- Create Total row
    INSERT INTO #BudgetEntryListingtest

    SELECT 'TOTAL',
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           dbo.fn_GetBudgetTotalAmount(@FromDate, @ToDate) As Amount
    FROM t_BudgetManualEntry E


    SELECT *
    FROM #BudgetEntryListingtest
    ORDER BY CASE WHEN Budget = 'TOTAL' THEN 1 ELSE 0 END,
             CreatedOn
END

GO
