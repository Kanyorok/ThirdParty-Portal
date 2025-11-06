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
        DB::unprepared("cREATE      PROC [dbo].[r_TenantMaintenance]     
AS BEGIN       
CREATE TABLE #TenantMaintenance        
( TenantType  VARCHAR(200),      
Name  VARCHAR(200),     
ID          VARCHAR(200),       
    
Phone       VARCHAR(200),      
Email       VARCHAR(200),      
Remarks     VARCHAR(200)      
 )       
INSERT INTO #TenantMaintenance     
(TenantType,     
Name,      
ID,              
Phone,      
Email,     
Remarks     
)     
SELECT     
CD.Description AS TenantType,      
TH.ThirdPartyName as Name,          
TH.RegistrationNumber as ID,      
TH.Phone AS Phone,       
TH.Email AS Email,       
TM.Remarks               
FROM t_TenantMaintenance AS TM        
JOIN t_CodeDetails CD ON TM.TenantType = CD.ID     
JOIN t_Thirdparties TH ON TH.id=TM.Id    
SELECT * FROM #TenantMaintenance;      
DROP TABLE #TenantMaintenance;    
END     ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_TenantMaintenance");
    }
};
