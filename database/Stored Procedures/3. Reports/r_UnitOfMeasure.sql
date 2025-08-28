CREATE OR ALTER PROCEDURE [dbo].[r_UnitOfMeasure]
AS
BEGIN
    SET NOCOUNT ON

    CREATE TABLE #UnitOfMeasure
    (
        --ID Varchar(200),
        Name       Varchar(200),
        CreatedBy  Varchar(200),
        CreatedOn  Date,
        ModifiedBy Varchar(20),
        ModifiedOn Date
    )

    INSERT INTO #UnitOfMeasure
    (
        --ID ,
        Name,
        CreatedBy,
        CreatedOn,
        ModifiedBy,
        ModifiedOn)
    SELECT
        --PU.ID ,
        PU.Name,
        U1.Name as CreatedBy,
        PU.CreatedOn,
        U2.Name as ModifiedBy,
        PU.ModifiedOn
    FROM t_UOM as PU
             JOIN t_Users as U1 ON U1.ID = PU.CreatedBy
             JOIN t_Users as U2 ON U2.ID = PU.ModifiedBy


    ORDER BY PU.ID

    SELECT * FROM #UnitOfMeasure (NOLOCK)

    SET NOCOUNT OFF
END


GO
