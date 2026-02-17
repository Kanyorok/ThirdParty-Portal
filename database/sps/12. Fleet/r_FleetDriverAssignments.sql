CREATE OR ALTER PROC [dbo].[r_FleetDriverAssignments]

AS
BEGIN
    SET NOCOUNT ON;
	   CREATE TABLE #FleetDriverAssignments               
    (                  
                     
        Vehicle   VARCHAR(200),
		Driver NVARCHAR(200),
		Assigned DATE,                              
        Unassigned  DATE, 
		Purpose VARCHAR(200),
		AssignedBy VARCHAR(200),
		Notes VARCHAR(200)
 
    );  

	INSERT INTO #FleetDriverAssignments
	 (                  
                      
        Vehicle,
		Driver,
		Assigned,                              
        Unassigned, 
		Purpose ,
		AssignedBy,
		Notes 
	 )
	 SELECT 
	 v.RegistrationNo AS Vehicle,
	 r.FullName,
	 d.AssignmentDate,
	 d.UnassignmentDate ,
	 d.Purpose,
	 u.Name,
	 d.Notes
	 

	 FROM t_FleetDriverAssignments AS d 
	 join t_FleetDrivers As r on r.Id = d.DriverID
	 join t_FleetVehicles As v on v.Id = d.VehicleID
	 join t_Users AS u on u.ID = d.AssignedBy
	

	   SELECT * FROM #FleetDriverAssignments
	DROP TABLE #FleetDriverAssignments

	END


	--EXEC r_FleetDriverAssignments


