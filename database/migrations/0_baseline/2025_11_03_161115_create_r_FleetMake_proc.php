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
        DB::unprepared(" Create     PROCEDURE r_FleetMake
-- (
--    @Status  VARCHAR(50) = NULL
--)
AS
BEGIN
    SET NOCOUNT ON;
	   CREATE TABLE #FleetMake         
    (       
	BrandID	VARCHAR(200),
	FleetBrand 	VARCHAR(200),
	CreatedBy VARCHAR(200)

    );  

	INSERT INTO #FleetMake    
	 (                  
    	BrandID, 
		FleetBrand,
		CreatedBy 
	 )
	 SELECT 
	b.BrandID,
	b.BrandName,
	c.[Name]
	 FROM t_FleetBrands AS b
	 left join t_Users AS c on c.Id = b.CreatedBy

	   

	   SELECT * FROM #FleetMake    
	DROP TABLE #FleetMake        


	END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_FleetMake");
    }
};
