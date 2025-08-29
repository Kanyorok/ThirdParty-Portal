CREATE OR ALTER PROCEDURE [dbo].[r_BudgetRateTypes]
AS
BEGIN
    CREATE TABLE #BudgetRateTypes
    (
        RatesCode   VARCHAR(200),
        RatesName   VARCHAR(200),
        Description VARCHAR(200)

    )

    INSERT INTO #BudgetRateTypes
    (RatesCode,
     RatesName,
     Description)
    SELECT RateTypeCode,
           RateTypeName,
           Description

    FROM t_BudgetRates


    SELECT * FROM #BudgetRateTypes;
END
--GO
