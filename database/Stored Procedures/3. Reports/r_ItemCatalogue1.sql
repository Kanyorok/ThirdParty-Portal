CREATE OR ALTER PROC [dbo].[R_ItemCatalogue1](
    @ItemCategory VARCHAR(200) = NULL
)
AS
BEGIN
    CREATE TABLE #ItemCatalogue
    (
        UniqueCode      NVARCHAR(50),
        ItemName        VARCHAR(100),
        Itemdescription VARCHAR(150),
        ItemType        VARCHAR(40),
        Category        VARCHAR(100),
        UOM             VARCHAR(20),
        Currency        VARCHAR(20),
        UnitPrice       MONEY,
        CreatedBy       VARCHAR(50),
        ModifiedBy      VARCHAR(50),
        Deletedby       VARCHAR(50)
    )

    INSERT INTO #ItemCatalogue
    SELECT c.ItemCode,
           c.ItemName,
           c.ItemDescription,
           c.ItemType,
           c.Category,
           c.UOM,
           '' AS Currency,
           c.UnitPrice,
           s.Name,
           c.ModifiedBy,
           c.DeletedBy
    FROM t_Items c
             INNER JOIN t_Users s ON c.CreatedBy = s.Id
    WHERE (@ItemCategory IS NULL OR @ItemCategory = 'ALL')
       OR c.Category IN (SELECT value
                         FROM STRING_SPLIT(@ItemCategory, ','))

    SELECT * FROM #ItemCatalogue
END
GO
