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
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_RFQresponse]
AS
BEGIN


    CREATE TABLE #RFQresponse
    (
        RFQNumber    NVARCHAR(50),
        SupplierName VARCHAR(100),
        TotalPayable MONEY,
        DurationDays VARCHAR(20),
        CreatedBy    NVARCHAR(50)

    )

    INSERT INTO #RFQresponse

    SELECT R.RFQNumber,
           R.SupplierName,
           R.TotalPayable,
           R.DurationDays,
           U.Name

    FROM t_RFQResponse R
             JOIN t_Users U on u.Id = R.CreatedBy

    SELECT * FROM #RFQresponse

END

--go
--exec r_RFQresponse

--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_RFQresponse");
    }
};
