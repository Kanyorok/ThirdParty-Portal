CREATE OR ALTER PROC [dbo].[r_InsuranceProductRiders] @IsActive VARCHAR(300) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #InsuranceProductRiders
    (
        Provider          VARCHAR(200),
        Product           VARCHAR(200),
        RiderName         VARCHAR(200),
        Description       VARCHAR(300),
        AdditionalPremium Varchar(200),
        IsOptional        VARCHAR(200),
        IsActive          VARCHAR(200),
        CreatedBy         varchar(300),
        CreatedOn         DATE
    );
    INSERT INTO #InsuranceProductRiders
    (Provider,
     Product,
     RiderName,
     Description,
     AdditionalPremium,
     IsOptional,
     IsActive,
     CreatedBy,
     CreatedOn)
    SELECT I.Name  as Provider,
           N.Name  as Product,
           R.RiderName,
           R.Description,
           R.AdditionalPremium,
           CASE R.IsOptional
               WHEN 1 THEN 'Yes'
               WHEN 0 THEN 'No'
               ELSE 'Unknown'
               END AS IsOptional,
           CASE R.IsActive
               WHEN 1 THEN 'Active'
               WHEN 0 THEN 'Inactive'
               ELSE 'Unknown'
               END AS IsActive,
           U.Name  AS CreatedBy,
           R.CreatedOn
    FROM t_InsuranceProductRiders as R
             join t_Users as U on U.Id = R.CreatedBy
             join t_InsuranceProducts as N ON N.Name = R.Product
             join t_InsuranceProviders as I on I.Id = R.Id

    WHERE @IsActive IS NULL
       OR @IsActive = 'ALL'
       OR (CASE I.IsActive
               WHEN 1 THEN 'Active'
               WHEN 0 THEN 'Inactive'
               ELSE 'Unknown'
        END) IN (SELECT LTRIM(RTRIM(value))
                 FROM STRING_SPLIT(@IsActive, ','));
    SELECT * FROM #InsuranceProductRiders

    DROP TABLE #InsuranceProductRiders
END
GO
