CREATE OR ALTER PROCEDURE [dbo].[r_BudgetActivityMaster]
AS
BEGIN
    CREATE TABLE #BudgetActivityMaster
    (
        ActivityName VARCHAR(200),
        BudgetLine   VARCHAR(200),
        Description  VARCHAR(200)

    )

    INSERT INTO #BudgetActivityMaster
    (ActivityName,
     BudgetLine,
     Description)
    SELECT BM.ActivityName,
           BL.LineName,
           BM.Description

    FROM t_BudgetActivityMaster AS BM
             JOIN t_BudgetLines BL ON BM.Id = BL.Id


    SELECT * FROM #BudgetActivityMaster;
END
--GO
