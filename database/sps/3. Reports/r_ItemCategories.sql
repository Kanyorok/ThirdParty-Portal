CREATE OR ALTER PROC [dbo].[r_ItemCategories] @FromDate DATE = Null,
                                              @ToDate Date = null
AS

BEGIN
    CREATE TABLE #ItemCategories
    (

        Name         NVARCHAR(200),
        Description  VARCHAR(200),
        CategoryCode NVARCHAR(200),
        CreatedOn    DATE,
        CreatedBy    VARCHAR(100)

    )

    INSERT INTO #ItemCategories

    SELECT I.Name,
           I.Description,
           I.CategoryCode,
           I.CreatedOn,
           U.Name

    FROM t_ItemCategories I
             JOIN t_Users U ON U.ID = I.CreatedBy
    WHERE (@FromDate IS NULL OR I.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR I.CreatedOn < DATEADD(DAY, 1, @ToDate));

    SELECT * FROM #ItemCategories

END

--GO

--EXEC  r_ItemCategories
--@FromDate = '1 jan 2025',
--@ToDate  = '21 Aug 2025'


--GO
