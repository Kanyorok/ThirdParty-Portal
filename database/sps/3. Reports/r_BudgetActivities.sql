CREATE or ALTER PROCEDURE [dbo].[r_BudgetActivities]
AS
BEGIN
    CREATE TABLE #BudgetActivities
    (
        BudgetName VARCHAR(200),

        [From]     DATETIME,
        [To]       DATETIME,
        Allocation VARCHAR(200),

        CreatedBy  VARCHAR(200)
    )

    INSERT INTO #BudgetActivities
    (BudgetName,
     [From],
     [To],
     Allocation,
     CreatedBy)
    SELECT NW.Name           as BudgetName,

           NW.[From],
           NW.[To],
           BA.FullAllocation AS Allocation,
           U.Name            as CreatedBy
    FROM t_Budgets AS NW
             JOIN t_Users U ON NW.Id = U.Id
             JOIN t_BudgetActivities BA ON NW.Id = BA.Id

    SELECT * FROM #BudgetActivities;
END
--GO
