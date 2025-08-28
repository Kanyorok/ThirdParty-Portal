CREATE OR ALTER PROC [dbo].[r_PropertyCategories] @FromDate SMALLDATETIME = NULL,
                                                  @ToDate SMALLDATETIME = NULL
AS
BEGIN

    CREATE TABLE #PropertyCategories
    (
        PropertyName Varchar(100),
        Description  Varchar(100),
        --Type			Varchar(100),
        --Code			Nvarchar(100),
        CreatedBy    Varchar(100),
        CreatedOn    Date
    )

    INSERT INTO #PropertyCategories
    SELECT C.Name,
           C.Description,
           --C.Type,
           --C.Code,
           U.Name as CreatedBy,
           C.CreatedOn

    from t_CategoryMaster C
             JOIN t_users U ON U.Id = C.CreatedBy

    WHERE (@FromDate IS NULL OR C.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR C.CreatedOn < DATEADD(DAY, 1, @ToDate))


    SELECT * FROM #PropertyCategories
END
--GO
--EXEC R_PropertyCategories


GO
