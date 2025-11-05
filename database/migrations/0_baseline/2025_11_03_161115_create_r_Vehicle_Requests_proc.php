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
        DB::unprepared("create   PROCEDURE [dbo].[r_Vehicle Requests]
(
    @Status VARCHAR(MAX) = Null
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #VehicleRequests

    (
        RequestID    VARCHAR(200),
        Requester     VARCHAR(200),
        Department	varchar(200),
        RequestDate  DATE,
        TripNo    VARCHAR(200),
		TripDate		 DATE,
		[Route]     Varchar(200),
		Passengers  INT,
		VehicleType Varchar(200),
		[Status] Varchar(200),
		Purpose Varchar (200)

      
    );

    INSERT INTO #VehicleRequests
    (
        
        RequestID,
        Requester,
        Department,
        RequestDate,
        TripNo,
		TripDate,
		[Route],
		Passengers,
		VehicleType,
		[Status],
		Purpose
    )  
    SELECT 
        r.RequestID,
		u.[Name] AS Requester ,
		t.[Name] AS Deparment,
		r.RequestDate,
		p.TripNo,
		r.TripDate,
		CONCAT(r.FromLocation, ' -> ', r.ToLocation) AS [route],
		r.PassengerCount AS Passengers,
		v.Description AS VehicleType,
		s.Description AS [Status],
		r.Purpose
        
    FROM t_FleetVehicleRequests AS r
	join t_Users As u on u.Id = r.RequestedBy
	join t_Departments AS t on t.Id = r.Department
	join t_TripLogs AS p on p.Id = r.TripNo
	join t_CodeDetails AS v on v.ID = r.PreferredVehicleType
	JOIN t_CodeDetails AS s ON s.Value = r.Status AND s.CodeID = 'VehicleRequestStatus'
	where
        @Status IS NULL OR @Status = 'ALL' OR s.Description = @Status;



    SELECT * FROM #VehicleRequests;

    DROP TABLE #VehicleRequests;
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_Vehicle Requests");
    }
};
