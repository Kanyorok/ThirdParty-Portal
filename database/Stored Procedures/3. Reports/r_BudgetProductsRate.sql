CREATE OR ALTER PROCEDURE [dbo].[r_BudgetProductsRate]
AS
BEGIN
    CREATE TABLE #BudgetProductsRate
    (
        PeriodType    NVARCHAR(510),
        ProductTypeID NVARCHAR(510),
        RateValue     DECIMAL,
        CreatedBy     NVARCHAR(510),
        EffectiveDate DATETIME,
        Source        VARCHAR(510),
        CreatedOn     DATETIME,


    )

    INSERT INTO #BudgetProductsRate
    (PeriodType,
     ProductTypeID,
     RateValue,
     CreatedBy,
     EffectiveDate,
     Source,
     CreatedOn)
    SELECT PT.PeriodType,
           BP.ProductTypeID,
           BD.RateValue,
           U.Name AS CreatedBy,
           BD.EffectiveDate,
           BD.Source,
           BD.CreatedOn
    FROM t_BudgetDriverRates BD
             JOIN t_BudgetPeriodTypes PT on PT.Id = BD.PeriodTypeID
             JOIN t_Users U on U.id = BD.Id
             JOIN t_BudgetProducts BP on BP.id = BD.PeriodTypeID

    SELECT * FROM #BudgetProductsRate
END

GO
