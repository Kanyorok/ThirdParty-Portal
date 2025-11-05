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
        DB::unprepared("Create   PROCEDURE [dbo].[r_FleetDriverLicenseTracking]

AS
BEGIN
    SET NOCOUNT ON;
	   CREATE TABLE #FleetDriverLicenseTracking                 
    (                  
                     
        LicenseNo   VARCHAR(200),
		Category VARCHAR(200),
		Issued DATE,                              
        Expiry   DATE,    
		Notes VARCHAR(200)
 
    );  

	INSERT INTO #FleetDriverLicenseTracking
	 (                  
                      
        LicenseNo,
		Category,
		Issued ,                              
        Expiry,    
		Notes 
	 )
	 SELECT 
	 d.LicenseNumber,
	 d.LicenseCategory,
	 d.IssueDate ,
	 d.Expirydate,
	 d.Notes
	 

	 FROM t_FleetDriverLicenseTracking AS d 
	 
	 --WHERE 
  --      @Status IS NULL 
  --      OR @Status = 'ALL'
  --      OR c.Description IN (SELECT value FROM STRING_SPLIT(@Status, ','))

	   SELECT * FROM #FleetDriverLicenseTracking
	DROP TABLE #FleetDriverLicenseTracking

	END


	--EXEC r_FleetDriverLicenseTracking


	--select * from t_FleetDriverLicenseTracking
	--select * from t_FleetDrivers");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_FleetDriverLicenseTracking");
    }
};
