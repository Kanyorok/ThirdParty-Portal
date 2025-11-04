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
        DB::unprepared("CREATE    PROC [dbo].[r_StockAdjustments] 

@FromDate smalldatetime = NULL,                                                 
@ToDate smalldatetime = NULL    

AS 
BEGIN     

SET NOCOUNT ON;      

CREATE TABLE #StockAdjustments (

        AdjustmentID   VARCHAR(200), 
		AdjustmentDate SMALLDATETIME,         
		Branch         VARCHAR(200),         
		Reason         VARCHAR(200),         
		AdjustedBy     VARCHAR(200),         
		Status         VARCHAR(200),         
		CreatedBy      VARCHAR(200),         
		CreatedOn      SMALLDATETIME    
		
		);     
		
INSERT INTO #StockAdjustments (AdjustmentID, AdjustmentDate, Branch, Reason, AdjustedBy, Status, CreatedBy, CreatedOn)     

SELECT 
  SA.AdjustmentID,            
  SA.AdjustmentDate,            
  B.Name AS Branch,            
  CD.Description AS Reason,
  U1.Name AS AdjustedBy,
  DC.Description AS Status,
  U2.Name AS CreatedBy,            
  SA.CreatedOn     
  
FROM t_StockAdjustments AS SA              
  
 JOIN t_Branches B ON B.ID = SA.Branch              
 JOIN t_StockAdjustmentItems SAI ON SAI.AdjustmentId = SA.Id
 JOIN t_CodeDetails CD ON CD.ID = SAI.Reason
 JOIN t_CodeDetails DC ON DC.Value = SA.Status
 JOIN t_Users U1 ON U1.ID = SA.AdjustedBy              
 JOIN t_Users U2 ON U2.ID = SA.CreatedBy     
 
 WHERE (@FromDate IS NULL OR SA.AdjustmentDate >= @FromDate) AND (@ToDate IS NULL OR SA.AdjustmentDate < DATEADD(DAY, 1, @ToDate))     
 --AND (@Status IS NULL OR @Status = 'ALL' OR CD.Description = @Status)     
 
 SELECT * FROM #StockAdjustments     
 
 END  
 
 --GO 


");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_StockAdjustments");
    }
};
