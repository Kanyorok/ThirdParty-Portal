Create OR ALTER PROCEDURE [dbo].[r_DriversList](
    @Status VARCHAR(50) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;
    CREATE TABLE #DriversList
    (
        DriverID          VARCHAR(200),
        DriverName        VARCHAR(200),
        LicenseNumber     VARCHAR(300),
        LicenseExpirydate DATE,
        EmploymentStatus  VARCHAR(300),
        Phone             Varchar(100)
    );

    INSERT INTO #DriversList
    (DriverID,
     DriverName,
     LicenseNumber,
     LicenseExpirydate,
     EmploymentStatus,
     Phone)
    SELECT d.DriverID          AS DriverID,
           d.DriverName        AS Name,
           d.LicenseNumber     AS LicenseNumber,
           d.LicenseExpirydate AS ExpiryDate,
           c.Description       AS EmployeeStatus,
           d.Phone                [Phone]
    FROM t_Drivers AS d
             left join t_CodeDetails AS c
                       on d.DriverID = c.value
    where @status IS NULL
       or @status = 'ALL'
       or @Status = c.Description
    --SELECT value
    --    FROM STRING_SPLIT(@Status, ',')


    SELECT * FROM #DriversList
    DROP TABLE #DriversList


END


--GO
