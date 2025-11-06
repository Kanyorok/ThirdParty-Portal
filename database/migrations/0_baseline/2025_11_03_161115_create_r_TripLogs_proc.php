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
        DB::unprepared("CREATE PROCEDURE [dbo].[r_TripLogs]
(
    @TripType     VARCHAR(500) = NULL,
    @StartDate    DATE = NULL,
    @EndDate      DATE = NULL,
    @VehicleType  VARCHAR(500) = NULL,
    @LoadType     VARCHAR(500) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #TripLogs
    (
        TripNo        VARCHAR(200),
        TripType      VARCHAR(200),
        VehicleType   VARCHAR(200),
        LoadType      VARCHAR(200),
        StartDate     DATE,
        EndDate       DATE,
        ChildTrips    VARCHAR(200)
    );

    INSERT INTO #TripLogs
    (
        TripNo,
        TripType,
        VehicleType,
        LoadType,
        StartDate,
        EndDate,
        ChildTrips 
    )
    SELECT 
        t.TripNo,
        c.[Description] AS TripType,
        v.[Description] AS VehicleType,
        l.[Description] AS LoadType,
        t.TripStartDate AS StartDate,
        t.TripEndDate AS EndDate,
        t.ParentTripID AS ChildTrips
    FROM t_TripLogs AS t
    JOIN t_CodeDetails AS c ON c.ID = t.TripType
    JOIN t_CodeDetails AS v ON v.ID = t.VehicleType
    JOIN t_CodeDetails AS l ON l.ID = t.LoadType
    WHERE 
        (@TripType IS NULL OR @TripType = 'ALL' OR c.Description IN (SELECT TRIM(value) FROM STRING_SPLIT(@TripType, ',')))
        AND (@VehicleType IS NULL OR v.Description IN (SELECT TRIM(value) FROM STRING_SPLIT(@VehicleType, ',')))
        AND (@LoadType IS NULL OR l.Description IN (SELECT TRIM(value) FROM STRING_SPLIT(@LoadType, ',')))
        AND (@StartDate IS NULL OR t.TripStartDate >= @StartDate)
        AND (@EndDate IS NULL OR t.TripEndDate <= @EndDate);

    SELECT * FROM #TripLogs;

    DROP TABLE #TripLogs;
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_TripLogs");
    }
};
