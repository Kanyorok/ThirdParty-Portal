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
        DB::unprepared("CREATE    PROCEDURE [dbo].[R_StockTake_test]
(
    @FromDate DATETIME = NULL,
    @ToDate DATETIME = NULL
)
AS    
BEGIN    
    SET NOCOUNT ON    
	

	
    CREATE TABLE #StockTake
    (      
        Branch		VARCHAR(200),
        Store		VARCHAR(200),
        CountedBy	VARCHAR(200),
        Date		DATETIME,
        PostedBy	VARCHAR(200),
        PostedOn	DATETIME
    )

    INSERT INTO #StockTake
    (
        Branch,
        Store,
        CountedBy,
        Date,
        PostedBy,
        PostedOn
    )
    SELECT 
        BR.BranchId ,
        K.StoreId,
       ST.CountedBy,
        ST.CountDate ,
        U.CreatedBy,
        ST.CreatedOn 
    FROM
        t_StockTake  ST 
		JOIN t_Branches BR ON ST.BRANCHID=BR.ID 
		JOIN t_Users U on U.ID=ST.CREATEDBY
		JOIN t_Stores K on K.Id=st.storeid
	
    WHERE
		   --ST.CreatedOn
		   --   BETWEEN
     --  @FromDate AND  @ToDate+1
	    (@FromDate IS NULL OR 	 ST.CreatedOn >= @FromDate) AND @ToDate	IS NULL OR
	ST.CreatedOn <@ToDate+1



    SELECT * FROM #StockTake
    
    DROP TABLE #StockTake

    SET NOCOUNT OFF    
END  

");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_StockTake_test");
    }
};
