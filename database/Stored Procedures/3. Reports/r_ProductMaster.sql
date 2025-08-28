CREATE OR ALTER PROC [dbo].[r_ProductMaster] @FromDate SMALLDATETIME= NULL,
                                             @ToDate SMALLDATETIME=NULL,
                                             @ProdID VARCHAR(100) = NULL,
                                             @Currency VARCHAR(100) = NULL
AS
BEGIN

    CREATE TABLE #R_ProductMaster
    (
        --Id				INT,
        CBSProductID  VARCHAR(100),
        Description   VARCHAR(200),
        ProductTypeID VARCHAR(100),
        CurrencyID    VARCHAR(100),
        GLAccountID   VARCHAR(100),
        CreatedBy     VARCHAR(100),
        CreatedOn     DATE

    )
-- Create table to store producttype filter
    DECLARE @ProdTypeTable TABLE
                           (
                               ProductTypeID VARCHAR(100)
                           );

    IF @ProdID IS NOT NULL
        BEGIN
            INSERT INTO @ProdTypeTable (ProductTypeID)
            SELECT TRIM(value)
            FROM STRING_SPLIT(@ProdID, ',');
        END
    -- Create table to store currency filter
    DECLARE @CurrencyTable TABLE
                           (
                               CurrencyID VARCHAR(100)
                           );

    IF @Currency IS NOT NULL
        BEGIN
            INSERT INTO @CurrencyTable (CurrencyID)
            SELECT TRIM(value)
            FROM STRING_SPLIT(@Currency, ',');
        END

    INSERT INTO #R_ProductMaster
    SELECT
        --B.Id,
        P1.ProductCode as CBSProductID,
        B.Description,
        P2.Name        as ProductTypeID,
        C.Code         as CurrencyID,
        GL.GTType      as GLAccountID,
        U.Name         as CreatedBy,
        B.CreatedOn

    FROM t_BudgetProducts B

             JOIN t_users U ON U.ID = B.CreatedBy
             JOIN t_BudgetGLAccounts GL ON GL.Id = B.GLAccountID
             JOIN t_BudgetProductTypes P1 ON P1.ID = B.CBSProductID
             JOIN t_BudgetProductTypes P2 ON P2.ProductCode = B.ProductTypeID
             JOIN t_Currencies C ON C.ID = B.CurrencyID

    WHERE (@FromDate IS NULL OR B.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR B.CreatedOn < DATEADD(DAY, 1, @ToDate))
      AND (
        @ProdID IS NULL
            OR EXISTS (SELECT 1
                       FROM @ProdTypeTable P
                       WHERE P.ProductTypeID = P2.Name)
        )
      AND (
        @Currency IS NULL
            OR EXISTS (SELECT 1
                       FROM @CurrencyTable T
                       WHERE T.CurrencyID = C.Code)
        );

    select * from #R_ProductMaster;

end
GO
