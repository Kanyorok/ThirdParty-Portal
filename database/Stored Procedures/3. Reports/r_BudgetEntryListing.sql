CREATE OR ALTER PROC [dbo].[R_BudgetEntryListing] @FromDate smalldatetime = null,
                                                  @ToDate smalldatetime = null
AS
BEGIN

    CREATE TABLE #BudgetEntryListing
    (
        Budget     VARCHAR(200),
        Branch     VARCHAR(100),
        BudgetLine VARCHAR(300),
        Amount     MONEY,
        Comments   VARCHAR(300),
        CreatedBy  VARCHAR(100),
        CreatedOn  DATE
    )

    INSERT INTO #BudgetEntryListing
    SELECT B.Name      as BudgetID,
           BR.Name     as BranchID,
           BL.LineName as BudgetLineID,
           E.Amount,
           E.Comments,
           U.Name      as CreatedBy,
           E.CreatedOn

    FROM t_BudgetManualEntry E
             JOIN t_users U ON U.ID = E.CreatedBy
             JOIN t_Branches BR ON BR.ID = E.BranchID
             JOIN t_budgets B ON B.ID = E.BudgetID
             JOIN t_BudgetLines BL ON BL.ID = E.BudgetLineID

    WHERE (@FromDate IS NULL OR E.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR E.CreatedOn < DATEADD(DAY, 1, @ToDate))

    SELECT * FROM #BudgetEntryListing
END

--GO
--EXEC R_BudgetEntryListing

--select * from t_BudgetManualEntry
--select * from t_branches
--select * from t_budgets
--select * from t_BudgetLines
GO
