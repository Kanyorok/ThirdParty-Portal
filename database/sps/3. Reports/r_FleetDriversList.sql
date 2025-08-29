Create OR ALTER PROCEDURE [dbo].[r_FleetDriversList](
    @Status VARCHAR(MAX) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;
    CREATE TABLE #FleetDriversList
    (

        FullName          VARCHAR(200),
        StaffNumber       BIGINT,
        NationalID        INT,
        LicenseNumber     VARCHAR(300),
        LicenseExpirydate DATE,
        Notes             VARCHAR(200),
        EmploymentType    NVARCHAR(255),
        Phone             Varchar(100)
    );

    INSERT INTO #FleetDriversList
    (FullName,
     StaffNumber,
     NationalID,
     LicenseNumber,
     LicenseExpirydate,
     Notes,
     EmploymentType,
     Phone)
    SELECT d.FullName          AS Name,
           d.StaffNumber       AS StaffNumber,
           d.NationalID        AS NationalID,
           'd.LicenseNumber'     AS LicenseNumber,
           'd.LicenseExpirydate' AS ExpiryDate,
           d.Notes             AS Notes,
           c.Description       AS EmploymentType,
           d.Phone               AS [Phone]
    FROM t_FleetDrivers AS d
             left join t_CodeDetails AS c on d.EmploymentType = c.ID
    WHERE @Status IS NULL
       OR @Status = 'ALL'
       OR c.Description IN (SELECT value FROM STRING_SPLIT(@Status, ','))

    SELECT * FROM #FleetDriversList
    DROP TABLE #FleetDriversList


END


--EXEC r_FleetDriversList

--GO
