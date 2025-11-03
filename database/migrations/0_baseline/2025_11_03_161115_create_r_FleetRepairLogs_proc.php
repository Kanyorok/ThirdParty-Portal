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
        DB::unprepared("Create   PROCEDURE [dbo].[r_FleetRepairLogs]
(
    @RepairType VARCHAR(MAX) = Null
)
AS
BEGIN
    SET NOCOUNT ON;
	   CREATE TABLE #FleetRepairLogs               
    (                                  
        Repair   NVARCHAR(200),
        Vehicle  NVARCHAR(200),                               
   		[Type] NVARCHAR(200),
		RepairDate Date,
        Vendor NVARCHAR(200),
		Cost DECIMAL(10,2),
		[Description] NVARCHAR(200)
    );  

	INSERT INTO #FleetRepairLogs
	 (                                 
       Repair,
        Vehicle,                               
   		[Type],
		RepairDate,
        Vendor,
		Cost,
		[Description]
		
	 )
	 SELECT 
	 r.RepairID As Repair,
	 v.RegistrationNo AS Vehicle,
	 c.[Description] As [Type],
	 r.RepairDate,
	 r.Vendor,
	 r.Cost,
	 r.[Description]
	
	
	 FROM t_FleetRepairLogs As r
	 join t_FleetVehicles AS v on v.Id = r.VehicleID
	 join t_CodeDetails AS c on c.ID = r.RepairType

	  WHERE (
        @RepairType IS NULL
        OR @RepairType = 'ALL'
        OR c.Description IN (
            SELECT LTRIM(RTRIM(value))
            FROM STRING_SPLIT(@RepairType, ',')
        )
    );


	   SELECT * FROM #FleetRepairLogs
	DROP TABLE #FleetRepairLogs


	END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_FleetRepairLogs");
    }
};
