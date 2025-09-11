CREATE OR ALTER PROC [dbo].[r_BudgetLines] @FromDate smalldatetime= null,
                                           @ToDate smalldatetime=null
AS
BEGIN

    CREATE TABLE #BudgetLines
    (
        BudgetLineCategory VARCHAR(100),
        LineName           VARCHAR(100),
        Department         VARCHAR(100),
        GLAccountType      VARCHAR(100),
        GLAccountSubType   VARCHAR(100),
        Description        VARCHAR(200),
        CreatedBy          VARCHAR(100),
        CreatedOn          DATE

    )

    INSERT INTO #BudgetLines
    SELECT BC.CategoryName       as BudgetLineCategoryID,
           B.LineName,
           D.Name                as DepartmentID,
           C.Description         AS GLAccountTypeID,
           SB.GLSubAccountTypeID AS GLAccountSubTypeID,
           B.Description,
           U.Name                as CreatedBy,
           B.CreatedOn

    FROM t_budgetlines B
             JOIN t_users U ON U.ID = B.CreatedBy
             JOIN t_BudgetLineCategories BC ON BC.ID = B.BudgetLineCategoryID
             JOIN t_Departments D ON D.ID = B.DepartmentID
             JOIN t_CodeDetails C ON C.Value = B.GLAccountTypeID AND C.CodeID = 'GLAccountType'
             JOIN t_BudgetGLSubTypes SB ON SB.Id = B.GLAccountSubTypeID
    WHERE (@FromDate IS NULL OR B.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR B.CreatedOn < DATEADD(DAY, 1, @ToDate))

    SELECT * FROM #BudgetLines
end
--GO
