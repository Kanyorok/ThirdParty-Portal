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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_FleetServiceAlerts] 
(
    @Status VARCHAR(MAX) = NULL,
    @MainteanceType VARCHAR(MAX) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #FleetServiceAlerts (
        Vehicle NVARCHAR(200),
        MaintenanceType NVARCHAR(200),
        [Description] NVARCHAR(200),
        Mileage DECIMAL(10,2),
        [Date] DATE,
        [Status] NVARCHAR(200)
    );

    -- Normalize input into lists
    DECLARE @StatusList TABLE (Value NVARCHAR(200));
    INSERT INTO @StatusList
    SELECT TRIM([value]) FROM STRING_SPLIT(@Status, ',') WHERE TRIM([value]) <> '';

    DECLARE @MainteanceTypeList TABLE (Value NVARCHAR(200));
    INSERT INTO @MainteanceTypeList
    SELECT TRIM([value]) FROM STRING_SPLIT(@MainteanceType, ',') WHERE TRIM([value]) <> '';

    INSERT INTO #FleetServiceAlerts (
        Vehicle,
        MaintenanceType,
        [Description],
        Mileage,
        [Date],
        [Status]
    )
    SELECT 
        v.RegistrationNo AS Vehicle,
        c.[Description] AS MaintenanceType,
        s.[Description],
        s.TriggerMileage AS Mileage,
        s.TriggerDate AS [Date],
        r.[Description] AS [Status]
    FROM t_FleetServiceAlerts AS s
    JOIN t_FleetVehicles AS v ON v.Id = s.VehicleID
    JOIN t_CodeDetails AS c ON c.ID = s.AlertType
    JOIN t_CodeDetails AS r ON r.ID = s.MaintenanceStatus
    WHERE 
        (
            NOT EXISTS (SELECT 1 FROM @StatusList)
            OR r.[Description] IN (SELECT Value FROM @StatusList)
        )
        AND (
            NOT EXISTS (SELECT 1 FROM @MainteanceTypeList)
            OR c.[Description] IN (SELECT Value FROM @MainteanceTypeList)
        );

    SELECT * FROM #FleetServiceAlerts;

    DROP TABLE #FleetServiceAlerts;
END;

--EXEC [r_FleetServiceAlerts]");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_FleetServiceAlerts");
    }
};
