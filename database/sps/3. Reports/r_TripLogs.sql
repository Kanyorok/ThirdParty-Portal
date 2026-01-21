create OR ALTER PROCEDURE [dbo].[r_TripLogs](
    @Status VARCHAR(50) = Null
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #TripLogs
    (
        TripNo        VARCHAR(200),
        Vehicle       VARCHAR(200),
        Driver        varchar(200),
        DriverType    VARCHAR(200),
        StartDate     DATE,
        EndDate       DATE,
        StartLocation Varchar(200),
        EndLocation   Varchar(200),
        Purpose       Varchar(200)

    );

    INSERT INTO #TripLogs
    (TripNo,
     Vehicle,
     Driver,
     DriverType,
     StartDate,
     EndDate,
     StartLocation,
     EndLocation,
     Purpose)
    SELECT t.TripNo,
           v.RegistrationNo,
           d.DriverNo,
           ' dt.Description' AS DriverType,
           t.TripStartDate,
           t.TripEndDate,
           t.StartLocation,
           t.EndLocation,
           t.Purpose

    --cd.Description AS Active

    FROM t_TripLogs AS t
             join t_FleetVehicles AS v on v.Id = v.Id
             join t_ContractedDrivers AS d on d.Id = d.Id
    -- join t_CodeDetails AS dt on dt.ID = t.DriverType


    WHERE @Status IS NULL
       OR @Status = 'ALL'
    --OR dt.Description IN (SELECT value FROM STRING_SPLIT(@Status, ','))


    SELECT * FROM #TripLogs;

    DROP TABLE #TripLogs;
END


--GO
