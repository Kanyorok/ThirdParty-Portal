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
        DB::unprepared("Create   Procedure [dbo].[r_DMSTags] @FromDate smalldatetime=null,
                                            @ToDate smalldatetime = null
AS
BEGIN
    CREATE TABLE #DMSTags
    (
        TagID       Varchar(100),
        Name        Varchar(200),
        Visibility  Varchar(100),
        Description Varchar(100),
        CreatedBy   Varchar(100),
        CreatedOn   Date

    )

    Insert Into #DMSTags
    Select DT.TagID,
           DT.Name,
           DT.Visibility,
           DT.Description,
           U.Name as CreatedBy,
           DT.CreatedOn

    From t_DMSTags DT
             Join t_Users U ON U.ID = DT.CreatedBy

    Where (@FromDate IS Null or DT.CreatedOn >= @FromDate)
      AND (@ToDate IS Null or DT.CreatedOn < DATEADD(Day, 1, @ToDate))
      AND DT.DeletedOn is null
    order by DT.CreatedOn


    Select * from #DMSTags;
END


--exec r_DMSTags


--select * from t_DMSTags
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_DMSTags");
    }
};
