CREATE OR ALTER PROC [dbo].[r_InsuranceProviders] @IsActive VARCHAR(300) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #InsuranceProviders
    (
        ProviderNumber VARCHAR(200),
        Name           VARCHAR(200),
        Country        VARCHAR(300),
        ContactPerson  VARCHAR(300),
        Phone          VARCHAR(200),
        IsActive       VARCHAR(200),
        CreatedBy      VARCHAR(300),
        CreatedOn      DATE
    );
    INSERT INTO #InsuranceProviders
    (ProviderNumber,
     Name,
     Country,
     ContactPerson,
     Phone,
     IsActive,
     CreatedBy,
     CreatedOn)
    SELECT I.InsuranceProviderNO as ProviderNumber,
           I.Name,
           I.Country,
           I.ContactPerson,
           I.Phone,
           CASE I.IsActive
               WHEN 1 THEN 'Active'
               WHEN 0 THEN 'Inactive'
               ELSE 'Unknown'
               END               AS IsActive,
           U.Name                AS CreatedBy,
           I.CreatedOn
    FROM t_insuranceproviders as I
             join t_Users as U on U.Id = i.CreatedBy

    WHERE @IsActive IS NULL
       OR @IsActive = 'ALL'
       OR (CASE I.IsActive
               WHEN 1 THEN 'Active'
               WHEN 0 THEN 'Inactive'
               ELSE 'Unknown'
        END) IN (SELECT LTRIM(RTRIM(value))
                 FROM STRING_SPLIT(@IsActive, ','));
    SELECT * FROM #InsuranceProviders

    DROP TABLE #InsuranceProviders
END
--GO
