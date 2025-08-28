CREATE OR ALTER PROCEDURE [dbo].[r_PropertyListing] @PropertyTypeName VARCHAR(200) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #SelectedTypes
    (
        PropertyTypeName VARCHAR(200)
    );


    IF @PropertyTypeName IS NOT NULL
        BEGIN
            INSERT INTO #SelectedTypes (PropertyTypeName)
            SELECT LTRIM(RTRIM(value))
            FROM STRING_SPLIT(@PropertyTypeName, ',');
        END

    CREATE TABLE #PropertyListing
    (
        PropertyName     VARCHAR(200),
        PropertyCode     VARCHAR(200),
        PropertyTypeName VARCHAR(200),
        Category         VARCHAR(200),
        Owner            VARCHAR(200),
        AcquisitionDate  DATE,
        Country          VARCHAR(200),
        TownCity         VARCHAR(200),
        CreatedBy        VARCHAR(200),
        CreatedOn        DATE
    )

    INSERT INTO #PropertyListing
    (PropertyName,
     PropertyCode,
     PropertyTypeName,
     Category,
     Owner,
     AcquisitionDate,
     Country,
     TownCity,
     CreatedBy,
     CreatedOn)
    SELECT PR.PropertyName,
           PR.PropertyCode,
           PT.PropertyTypeName,
           CM.Name AS Category,
           PR.Owner,
           PR.AcquisitionDate,
           PR.Country,
           L.Name  AS TownCity,
           U.Name  AS CreatedBy,
           PR.CreatedOn
    FROM t_PropertyRegistry PR
             JOIN t_PropertyType PT ON PT.ID = PR.PropertyType
             JOIN t_CategoryMaster CM ON CM.ID = PR.Category
             JOIN t_Localities L ON L.ID = PR.TownCity
             JOIN t_Users U ON U.Id = PR.CreatedBy
    --WHERE (@PropertyTypeName IS NULL OR PT.PropertyTypeName = @PropertyTypeName)
    -- WHERE (@PropertyTypeName = 'ALL' OR PT.PropertyTypeName = @PropertyTypeName)
    --WHERE PT.PropertyTypeName in (@PropertyTypeName)
    WHERE (
              @PropertyTypeName IS NULL
                  OR EXISTS (SELECT 1
                             FROM #SelectedTypes st
                             WHERE st.PropertyTypeName = PT.PropertyTypeName)
              );
    SELECT * FROM #PropertyListing

    SET NOCOUNT OFF
END
GO
