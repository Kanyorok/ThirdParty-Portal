CREATE OR ALTER PROCEDURE [dbo].[r_PropertyType]
AS
BEGIN
    SET NOCOUNT ON

    CREATE TABLE #PropertyType
    (
        ID          VARCHAR(200),
        Name        VARCHAR(200),
        Category    VARCHAR(200),
        Description VARCHAR(200),
        CreateOn    DATE,
        CreatedBy   VARCHAR(200),

    )

    INSERT INTO #PropertyType
    (ID,
     Name,
     Category,
     Description,
     CreateOn,
     CreatedBy)
    SELECT P.Id,
           P.PropertyTypeName,
           CM.Name as Category,
           P.Description,
           P.CreatedOn,
           U.Name  as CreatedBy

    FROM t_propertytype AS P
             JOIN t_Users AS U ON P.Id = U.ID
             JOIN t_CategoryMaster AS CM ON P.Id = CM.ID


    ORDER BY P.Id

    SELECT * FROM #PropertyType

    DROP TABLE #PropertyType

    SET NOCOUNT OFF
END
--GO
