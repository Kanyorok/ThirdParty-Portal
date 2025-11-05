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
        DB::unprepared("CREATE    PROCEDURE [dbo].[r_Repositories](
    @FromDate smallDatetime = null,
    @ToDate smallDatetime=null
)
AS
BEGIN
    CREATE TABLE #Repositories
    (
        Id   VARCHAR(200),
        Name         VARCHAR(200),
        Description     VARCHAR(200),
        Visibility   VARCHAR(200),
        Documents   VARCHAR(200),
        CreatedBy    VARCHAR(200),
        CreatedOn    DATE,
        ModifiedBy   VARCHAR(200),
        ModifiedOn   DATE
    )

 
   INSERT INTO #Repositories
    (Id,
     Name,
     Description,
     Visibility,
	 CreatedBy,
     Documents,
     CreatedOn,
     ModifiedBy,
     ModifiedOn
	 )

	
	 SELECT 
	 r.Id,
	 r.Name,
	 r.Description,
	 r.Visibility,
	 uc.name,
	 count(d.name),
	 r.CreatedOn,
	 um.name,
	 r.ModifiedOn
	 FROM t_Repositories r
	
	 INNER JOIN t_Documents d on d.RepositoryId = r.Id
	 LEFT JOIN t_Users uc on uc.id=r.CreatedBy
	 LEFT JOIN t_Users um on um.id = r.ModifiedBy

	 WHERE (@FromDate IS NULL OR r.CreatedOn >= @FromDate)
     AND (@ToDate IS NULL OR r.CreatedOn < DATEADD(DAY, 1, @ToDate))
     AND r.DeletedOn is null
	 
	 GROUP BY 
	  r.Id,
	  r.Name,
	  r.Description,
	  r.Visibility,
	  uc.Name,
	  r.CreatedOn,
	  um.Name,
	  r.ModifiedOn
	  	

    order by r.CreatedOn desc


    SELECT * FROM #Repositories
END


--GO


--exec [r_Repositories] @FromDate='01 Jun 2019',@ToDate='10 Sep 2025' ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_Repositories");
    }
};
