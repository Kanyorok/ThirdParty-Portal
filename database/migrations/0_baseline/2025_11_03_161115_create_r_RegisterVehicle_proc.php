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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_RegisterVehicle](
    @VehicleStatus VARCHAR(MAX) = NULL,
    @Branch VARCHAR(MAX) = NULL
    --@VehicleType VARCHAR(50) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #FleetRegisterVehicle
    (
        REGNO    NVARCHAR(200),
        Make     NVARCHAR(200),
        Model    NVARCHAR(200),
        [Type]   NVARCHAR(300),
        Fuel     NVARCHAR(200),
		Branch   NVARCHAR(200),
        [Status] NVARCHAR(200)
        
    );

    INSERT INTO #FleetRegisterVehicle
    (REGNO,
     Make,
     Model,
     [Type],
     Fuel,
	 Branch,
     [Status]
    
	 )
    SELECT v.RegistrationNo AS REGNO,
           br.BrandName     AS Make,
           m.ModelName      AS Model,
           c.Description    AS [Type],
           f.FuelName       AS Fuel,
		    b.Name           AS Branch,
           cd.Description   AS [Status]
          

    FROM t_FleetVehicles AS v
             JOIN t_FleetBrands AS br ON br.Id = v.Make
             JOIN t_FleetModels AS m ON m.Id = v.Model
             JOIN t_FuelTypes AS f ON f.Id = v.FuelType
             JOIN t_Branches AS b ON b.ID = v.AssignedBranch
             JOIN t_CodeDetails AS cd ON cd.ID = v.[Status]
             JOIN t_CodeDetails AS c ON c.ID = v.VehicleType
    WHERE (
        @VehicleStatus IS NULL
            OR @VehicleStatus = 'ALL'
            OR cd.Description IN (SELECT TRIM(value) FROM STRING_SPLIT(@VehicleStatus, ','))
        )
      AND (
        @Branch IS NULL
     
       OR @Branch = 'ALL'
            OR b.Name IN (SELECT TRIM(value) FROM STRING_SPLIT(@Branch, ','))
        );

    SELECT * FROM #FleetRegisterVehicle;

    DROP TABLE #FleetRegisterVehicle;
END

");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_RegisterVehicle");
    }
};
