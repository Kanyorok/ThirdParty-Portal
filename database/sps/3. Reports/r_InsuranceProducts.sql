CREATE OR ALTER PROC [dbo].[r_InsuranceProducts] @IsActive VARCHAR(300) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #InsuranceProducts
    (
        InsuranceProviderID VARCHAR(200),
        Name                VARCHAR(200),
        Type                VARCHAR(300),
        Description         VARCHAR(300),
        IsActive            VARCHAR(200),
        CreatedBy           VARCHAR(300),
        CreatedOn           DATE
    );
    INSERT INTO #InsuranceProducts
    (InsuranceProviderID,
     Name,
     Type,
     Description,
     IsActive,
     CreatedBy,
     CreatedOn)
    SELECT I.InsuranceProviderNO as InsuranceProviderID,
           N.Name,
           N.Type,
           N.Description,
           CASE I.IsActive
               WHEN 1 THEN 'Active'
               WHEN 0 THEN 'Inactive'
               ELSE 'Unknown'
               END               AS IsActive,
           U.Name                AS CreatedBy,
           N.CreatedOn
    FROM t_insuranceproducts as N
             join t_insuranceproviders as I on I.Id = N.InsuranceProviderID

             join t_Users as U on U.Id = i.CreatedBy

    WHERE @IsActive IS NULL
       OR @IsActive = 'ALL'
       OR (CASE I.IsActive
               WHEN 1 THEN 'Active'
               WHEN 0 THEN 'Inactive'
               ELSE 'Unknown'
        END) IN (SELECT LTRIM(RTRIM(value))
                 FROM STRING_SPLIT(@IsActive, ','));
    SELECT * FROM #InsuranceProducts

    DROP TABLE #InsuranceProducts
END
--GO
