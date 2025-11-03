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
        DB::unprepared("Create Procedure [dbo].[r_vehiclerequest]
(
	@Status VARCHAR(50) =NULL
)
AS
BEGIN
	SET NOCOUNT ON;
		CREATE TABLE #FleetVehicleRequest
	(
		RequestID VARCHAR(200),
		Requester VARCHAR(200),
		Department VARCHAR(200),
		VehicleType VARCHAR(200),
		[Status] VARCHAR(200)
	
	);

	INSERT INTO #FleetVehicleRequest
	(
		RequestID,
		Requester,
		Department,
		VehicleType,
		[Status] 
		
	)
	SELECT
		RequestID,
		vr.RequestedBy AS Requester,
		vr.Department,
		vr.PreferredVehicleType as Vehicleype,
		[Status]
		FROM t_FleetVehicleRequests vr
		join t_Departments d on d.id=vr.Department
		join t_CodeDetails cd on cd.Value=vr.PreferredVehicleType
		join t_CodeDetails c on c.Value= vr.Status  
		where @Status is NULL

	SELECT * FROM #FleetVehicleRequest
	DROP TABLE #FleetVehicleRequest

	END

");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_vehiclerequest");
    }
};
