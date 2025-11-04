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
        DB::unprepared("Create   PROCEDURE [dbo].[r_FleetVehicleInspections]
(
    @InspectionType VARCHAR(MAX) = Null
)

AS
BEGIN
    SET NOCOUNT ON;
	   CREATE TABLE #FleetVehicleInspections               
    (                                  
        InspectionID   VARCHAR(200),
		InspectionDate DATE,
		InspectionType NVARCHAR(200),
        Vehicle  NVARCHAR(200),                               
        Driver   NVARCHAR(200),    
		Mileage BIGINT,
		Fuel DECIMAL (10,2),
        EngineOil DECIMAL (10,2),
		Coolant DECIMAL (10,2)
    );  

	INSERT INTO #FleetVehicleInspections
	 (                                 
         InspectionID,
		InspectionDate,
		InspectionType,
        Vehicle,                               
        Driver,    
		Mileage,
		Fuel,
        EngineOil,
		Coolant
		
	 )
	 SELECT 
	 i.InspectionID,
	 i.InspectionDate,
	 c.Description,
	 v.RegistrationNo,
	 d.FullName,
	 i.Mileage,
	 i.Fuel,
	 i.EngineOil,
	 Coolant
	
	 FROM t_FleetVehicleInspections AS i 
	 join t_CodeDetails AS c on c.ID = i.InspectionTypeID
	 join t_FleetVehicles AS v on v.Id = i.VehicleID
	 join t_FleetDrivers AS d on d.Id = i.DriverID

	 WHERE (
        @InspectionType IS NULL
        OR @InspectionType = 'ALL'
        OR c.Description IN (
            SELECT LTRIM(RTRIM(value))
            FROM STRING_SPLIT(@InspectionType, ',')
        )
    );

	   SELECT * FROM #FleetVehicleInspections
	DROP TABLE #FleetVehicleInspections


	END



");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_FleetVehicleInspections");
    }
};
