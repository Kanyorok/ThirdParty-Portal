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
        DB::unprepared("CREATE   PROC [dbo].[r_Permissions](
    @CreatedOn smalldatetime
)
AS
BEGIN

    Create table #Permissions

    (

        Name       VarChar(200),
        GuardName  Varchar(50),
        CreateDate smalldatetime
    )

    Insert Into #Permissions
    select name,
           guard_name,
           created_at
    from t_Permissions
    Where cast(created_at as date) = @CreatedOn


    select * from #Permissions

END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_Permissions");
    }
};
