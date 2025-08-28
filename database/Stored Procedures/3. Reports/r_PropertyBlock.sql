CREATE OR ALTER PROCEDURE [dbo].[r_PropertyBlock]
AS
BEGIN
    SET NOCOUNT ON

    CREATE TABLE #PropertyBlock
    (
        Property    VARCHAR(200),
        BlockName   VARCHAR(200),
        Description VARCHAR(200),
        CreateOn    DATE,
        CreatedBy   VARCHAR(200),

    )

    INSERT INTO #PropertyBlock
    (Property,
     BlockName,
     Description,
     CreateOn,
     CreatedBy)
    SELECT R.PropertyName as Property,
           B.BlockName,
           B.Description,
           B.CreatedOn,
           U.Name         as CreatedBy

    FROM t_PropertyBlock AS B
             JOIN t_Users AS U ON B.Id = U.ID
             JOIN t_PropertyRegistry AS R ON R.Id = B.Id


    ORDER BY B.Id

    SELECT * FROM #PropertyBlock

    DROP TABLE #PropertyBlock

    SET NOCOUNT OFF
END

GO
