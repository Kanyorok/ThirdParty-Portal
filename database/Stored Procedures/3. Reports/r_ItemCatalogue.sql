CREATE OR ALTER PROC [dbo].[R_ItemCatalogue](
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
    SELECT C.ItemCode,
           C.ItemName,
           C.ItemDescription,
           IT.TypeName AS [Item Type],
           IC.Name     as Category,
           UM.Code     AS UOM,
           ''          AS Currency,
           C.ItemPrice,
           U.Name,
           C.ModifiedBy,
           C.DeletedBy
    FROM t_Items C
             INNER JOIN t_Users U ON c.CreatedBy = U.Id
             JOIN t_ItemCategories IC ON IC.Id = C.Category
             JOIN t_ItemTypes IT ON IT.Id = C.ItemType
             JOIN t_UOM UM ON UM.Id = C.UOM
    WHERE (@ItemCategory IS NULL OR @ItemCategory = 'ALL')
       OR c.Category IN (SELECT value
                         FROM STRING_SPLIT(@ItemCategory, ','))

    SELECT * FROM #ItemCatalogue
END
GO
