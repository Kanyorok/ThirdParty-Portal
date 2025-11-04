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
        DB::unprepared("create     PROCEDURE [dbo].[r_FleetInsuranceList](
    @Status VARCHAR(100) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #FleetInsuranceList
    (
        FleetInsuraceNo VARCHAR(200),
        Vehicle         VARCHAR(200),
        PolicyNo       VARCHAR(200),
        [Provider]      VARCHAR(200),
        Premium         DECIMAL(16, 4),
        [Start]         DATE,
        [End]           DATE,
        [Status]        VARCHAR(200)

    );

    INSERT INTO #FleetInsuranceList
    (FleetInsuraceNo
,
     Vehicle,
     PolicyNo,
     [Provider],
     Premium,
     [Start],
     [End],
     [Status])
    SELECT i.InsuranceNo,
           v.RegistrationNo AS VehicleID,
           i.PolicyNumber,
           p.[Name],
           i.PremiumAmount,
           i.CoverageStartDate,
           i.CoverageEndDate,
           cd.[Description]   AS [Status]

    FROM t_FleetInsuranceTracker AS i
             JOIN t_FleetVehicles AS v ON v.Id = i.VehicleID
             JOIN t_CodeDetails AS cd ON cd.ID = v.[Status]
			 join t_InsuranceProviders AS p on p.ID = i.InsuranceProvider


    WHERE @Status IS NULL
       OR @Status = 'ALL'
       OR cd.Description IN (SELECT value FROM STRING_SPLIT(@Status, ','))

    SELECT * FROM #FleetInsuranceList;

    DROP TABLE #FleetInsuranceList;
END


--GO


");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_FleetInsuranceList");
    }
};
