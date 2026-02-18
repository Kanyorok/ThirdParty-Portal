CREATE OR ALTER PROCEDURE [dbo].[r_FleetVehicleAssignments]

AS
BEGIN
    SET NOCOUNT ON;
	   CREATE TABLE #FleetVehicleAssignments             
    (                  
                     
        TripNo  NVARCHAR(200),
		VehicleType  NVARCHAR(200),
		RegistrationNo NVARCHAR(200),
		AssignedTo NVARCHAR(200),                              
        LastInspectionDate  DATE, 
		AssignmentDate DATE,
		Purpose VARCHAR(200),
		Notes VARCHAR(200),
		AssignedBy VARCHAR(200)
 
    );  

	INSERT INTO #FleetVehicleAssignments
	 (                  
                      
        TripNo,
		VehicleType,
		RegistrationNo,
		AssignedTo,                              
        LastInspectionDate, 
		AssignmentDate,
		Purpose ,
		Notes,
		AssignedBy
	 )
	 SELECT 
	  t.TripNo AS TripNo ,
	  c.[Description] AS VehicleType,
	  v.RegistrationNo AS RegistrationNo,
	  d.FullName AS AssignedTo ,
	  a.LastInspectionDate AS LastInspectionDate, 
	  a.AssignmentDate AS AssignmentDate,
	  a.Purpose AS Purpose ,
	  a.Notes As Notes,
	  u.UserID AS AssignedBy

	  from t_FleetVehicleAssignments AS a
		JOIN t_TripLogs t ON TRY_CONVERT(BIGINT, a.TripNo) = t.Id
		join t_FleetVehicles AS v on TRY_CONVERT(BIGINT, a.VehicleID) = v.Id
		join t_CodeDetails AS c on a.VehicleType = c.ID
		JOIN t_FleetDrivers AS d ON a.DriverID  = d.Id
		join t_Users AS u on a.AssignedBy = u.Id
	
	

	   SELECT * FROM #FleetVehicleAssignments
	DROP TABLE #FleetVehicleAssignments

	END



	--EXEC [r_FleetVehicleAssignments]


	




