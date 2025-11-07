CREATE OR ALTER PROCEDURE [dbo].[r_RegisterVehicle](
    @VehicleStatus VARCHAR(MAX) = NULL,
    @Branch VARCHAR(MAX) = NULL
    --@VehicleType VARCHAR(50) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #FleetRegisterVehicle
    (
        REGNO    VARCHAR(200),
        Make     VARCHAR(200),
        Model    VARCHAR(200),
        [Type]   VARCHAR(300),
        Fuel     VARCHAR(200),
        [Status] VARCHAR(200),
        Branch   VARCHAR(200),
        IsActive NVARCHAR(200)
    );

    INSERT INTO #FleetRegisterVehicle
    (REGNO,
     Make,
     Model,
     [Type],
     Fuel,
     [Status],
     Branch,
     IsActive)
    SELECT v.RegistrationNo AS REGNO,
           br.BrandName     AS Make,
           m.ModelName      AS Model,
           c.Description    AS [Type],
           f.FuelName       AS Fuel,
           cd.Description   AS [Status],
           b.Name           AS Branch,
           v.IsActive
    FROM t_FleetVehicles AS v
             JOIN t_FleetBrands AS br ON br.Id = v.Make
             JOIN t_FleetModels AS m ON m.Id = v.Model
             JOIN t_FuelTypes AS f ON f.Id = v.FuelType
             JOIN t_Branches AS b ON b.BranchID = v.AssignedBranch
             JOIN t_CodeDetails AS cd ON cd.ID = v.[Status]
             JOIN t_CodeDetails AS c ON c.ID = v.VehicleType
    WHERE (
        @VehicleStatus IS NULL
            OR @VehicleStatus = 'ALL'
            OR cd.Description IN (SELECT TRIM(value) FROM STRING_SPLIT(@VehicleStatus, ','))
        )
      AND (
        @Branch IS NULL
            OR @Branch = 'ALL'
            OR b.Name IN (SELECT TRIM(value) FROM STRING_SPLIT(@Branch, ','))
        );

    SELECT * FROM #FleetRegisterVehicle;

    DROP TABLE #FleetRegisterVehicle;
END
--GO
