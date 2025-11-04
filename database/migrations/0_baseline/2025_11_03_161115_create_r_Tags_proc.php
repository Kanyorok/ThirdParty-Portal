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
        DB::unprepared("Create    Procedure [dbo].[r_Tags] @FromDate smalldatetime=null,
                                         @ToDate smalldatetime = null
AS
BEGIN
    CREATE TABLE #Tags
    (
        TagID       Varchar(100),
        Name        Varchar(200),
        Visibility  Varchar(100),
        Description Varchar(100),
        CreatedBy   Varchar(100),
        CreatedOn   Date

    )

    Insert Into #Tags
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

    Select * from #Tags;
END
--GO

select u.Name from t_DMSTags t
LEFT JOIN t_users u on t.CreatedBy=u.Id

select * from t_Reports where ModuleId =700000

select * from t_Repositories

exec r_Tags @FromDate='01 Sep 2025', @ToDate='11 Sep 2025'

");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_Tags");
    }
};
