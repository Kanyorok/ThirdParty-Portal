<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("Create   PROCEDURE [dbo].[r_FleetContractedDriverAssignments]

AS
BEGIN
    SET NOCOUNT ON;
	   CREATE TABLE #FleetContractedDriverAssignments               
    (                  
                     
        Vehicle   VARCHAR(200),
		Assigned DATE,                              
        Unassigned  DATE, 
		Purpose VARCHAR(200),
		AssignedBy VARCHAR(200),
		Notes VARCHAR(200)
 
    );  

	INSERT INTO #FleetContractedDriverAssignments
	 (                  
                      
         Vehicle,
		Assigned,                              
        Unassigned, 
		Purpose ,
		AssignedBy,
		Notes 
	 )
	 SELECT 
	 r.RegistrationNo AS Vehicle,
	 d.AssignmentDate,
	 d.UnassignmentDate ,
	 d.Purpose,
	 u.[Name] AS AssignedBy,
	 d.Notes
	 

	 FROM t_FleetContractedDriverAssignments AS d 
	 join t_FleetVehicles As r on r.Id = d.VehicleID
	join t_Users AS u on u.ID = d.AssignedBy

	   SELECT * FROM #FleetContractedDriverAssignments
	DROP TABLE #FleetContractedDriverAssignments

	END



");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_FleetContractedDriverAssignments");
    }
};
