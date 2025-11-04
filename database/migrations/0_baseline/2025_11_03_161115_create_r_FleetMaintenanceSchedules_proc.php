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
        DB::unprepared("Create   PROCEDURE [dbo].[r_FleetMaintenanceSchedules]
(
    @Status VARCHAR(MAX) = Null
)
AS
BEGIN
    SET NOCOUNT ON;
	   CREATE TABLE #FleetMaintenanceSchedules               
    (                                  
        ScheduleID   NVARCHAR(200),
        Vehicle  NVARCHAR(200),                               
        MaintenanceType   NVARCHAR(200),    
		ScheduleDate Date,
		ScheduleMileage DECIMAL (10,2),
        MaintenanceStatus NVARCHAR(200),
		ActiveStatus NVARCHAR(200),
		Notes NVARCHAR(200)
    );  

	INSERT INTO #FleetMaintenanceSchedules
	 (                                 
        ScheduleID,
        Vehicle,                               
        MaintenanceType,    
		ScheduleDate,
		ScheduleMileage,
        MaintenanceStatus,
		ActiveStatus,
		Notes
		
	 )
	 SELECT 
	 m.ScheduleID,
	 v.RegistrationNo AS Vehicle,
	 c.Description As MaintenanceType,
	 m.ScheduledDate,
	 m.ScheduledMileage,
	 s.Description AS MaintenanceStatus,
	 m.status,
	 m.Notes
	
	 FROM t_FleetMaintenanceSchedules AS m
	 join t_FleetVehicles AS v on v.Id = m.VehicleID
	 join t_CodeDetails AS c on c.ID = m.MaintenanceType
	 join t_CodeDetails AS s on s.ID = m.MaintenanceStatus


 WHERE (
        @Status IS NULL
        OR @Status = 'ALL'
        OR s.Description IN (
            SELECT LTRIM(RTRIM(value))
            FROM STRING_SPLIT(@Status, ',')
        )
    );

	   SELECT * FROM #FleetMaintenanceSchedules
	DROP TABLE #FleetMaintenanceSchedules


	END

");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_FleetMaintenanceSchedules");
    }
};
