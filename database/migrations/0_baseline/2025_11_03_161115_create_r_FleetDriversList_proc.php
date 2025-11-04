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
        DB::unprepared("Create    PROCEDURE [dbo].[r_FleetDriversList](
    @EmploymentType VARCHAR(MAX) = NULL
)
AS
BEGIN
    SET NOCOUNT ON;
    CREATE TABLE #FleetDriversList
    (

        FullName          VARCHAR(200),
		StaffNO        NVARCHAR(200), 
        nationalID        NVARCHAR(200),
		Phone             Varchar(100),
        Employment   NVARCHAR(255),
        [Status] NVARCHAR(200),
		DriverAvailability NVARCHAR(200)
    );

    INSERT INTO #FleetDriversList
    (
	    FullName,
		StaffNO,
        nationalID,
		Phone,
        Employment,
        [Status],
		DriverAvailability
	 )
    SELECT d.FullName,
	       e.EmployeeID,
           d.NationalID,
		   d.Phone,
		   c.[Description] AS Employment,
		   CASE 
    WHEN d.IsActive = 1 THEN 'Active'
    ELSE 'Inactive'
END AS [Status],
		   s.[Description] AS DriverAvailability
     
          
    FROM t_FleetDrivers AS d 
join t_CodeDetails AS c on d.EmploymentType = c.ID
join t_Employees AS e on e.ID = d.StaffNumber
join t_CodeDetails As s on s.ID = d.DriverStatus

    WHERE @EmploymentType IS NULL
       OR @EmploymentType = 'ALL'
       OR c.Description IN (SELECT value FROM STRING_SPLIT(@EmploymentType, ','))

    SELECT * FROM #FleetDriversList
    DROP TABLE #FleetDriversList


END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_FleetDriversList");
    }
};
