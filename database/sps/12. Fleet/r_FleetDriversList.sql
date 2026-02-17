CREATE OR ALTER PROCEDURE [dbo].[r_FleetDriversList]
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #FleetDriversList
    (
        FullName          VARCHAR(200),
        StaffNumber       NVARCHAR(50),     
        NationalID        INT,
        Notes             VARCHAR(200),
        EmploymentType    NVARCHAR(255),
        Phone             VARCHAR(100),
        Status            NVARCHAR(50)
    );

    INSERT INTO #FleetDriversList
    (
        FullName,
        StaffNumber,
        NationalID,
        Notes,
        EmploymentType,
        Phone,
        Status
    )
    SELECT 
        d.FullName,
        e.EmployeeID,                    -- This is 'e00001', 'e00002', etc.
        d.NationalID,
        d.Notes,
        c.Description AS EmploymentType,
        d.Phone,
        CASE 
            WHEN d.IsActive = 1 THEN 'Active'
            ELSE 'Inactive'
        END AS Status
    FROM t_FleetDrivers AS d
    LEFT JOIN t_CodeDetails AS c ON d.EmploymentType = c.ID
    LEFT JOIN t_Employees AS e ON 'e' + RIGHT('0000' + CAST(d.StaffNumber AS NVARCHAR(10)), 5) = e.EmployeeID;
    -- Converts 5 -> 'e00005', 6 -> 'e00006', etc.

    SELECT * FROM #FleetDriversList;

    DROP TABLE #FleetDriversList;
END
GO

--EXEC [r_FleetDriversList]