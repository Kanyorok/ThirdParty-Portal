create OR ALTER PROCEDURE [dbo].[r_VehicleRequests](
    @Status VARCHAR(MAX) = Null
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #VehicleRequests

    (
        RequestID   VARCHAR(200),
        Requester   VARCHAR(200),
        Department  varchar(200),
        RequestDate DATE,
        TripNo      VARCHAR(200),
        TripDate    DATE,
        [Route]     Varchar(200),
        Passengers  INT,
        VehicleType Varchar(200),
        [Status]    Varchar(200),
        Purpose     Varchar(200)


    );

    INSERT INTO #VehicleRequests
    (RequestID,
     Requester,
     Department,
     RequestDate,
     TripNo,
     TripDate,
     [Route],
     Passengers,
     VehicleType,
     [Status],
     Purpose)
    SELECT r.RequestID,
           u.[Name]                                     AS Requester,
           t.[Name]                                     AS Deparment,
           r.RequestDate,
           p.TripNo,
           r.TripDate,
           CONCAT(r.FromLocation, ' -> ', r.ToLocation) AS [route],
           r.PassengerCount                             AS Passengers,
           v.Description                                AS VehicleType,
           s.Description                                AS [Status],
           r.Purpose
    FROM t_FleetVehicleRequests AS r
             join t_Users As u on u.Id = r.RequestedBy
             join t_Departments AS t on t.Id = r.Department
             join t_TripLogs AS p on p.Id = r.TripNo
             join t_CodeDetails AS v on v.ID = r.PreferredVehicleType
             JOIN t_CodeDetails AS s ON s.Value = r.Status AND s.CodeID = 'VehicleRequestStatus'
    WHERE (
              @Status IS NULL
                  OR Status = 'ALL'
                  OR s.Description IN (SELECT LTRIM(RTRIM(value))
                                       FROM STRING_SPLIT(@Status, ','))
              )


    SELECT * FROM #VehicleRequests;

    DROP TABLE #VehicleRequests;
END

GO
