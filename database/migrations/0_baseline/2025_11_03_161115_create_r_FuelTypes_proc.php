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
        DB::unprepared("CREATE    PROCEDURE r_FuelTypes
   
AS
BEGIN
    SET NOCOUNT ON;

    SELECT 
        f.FuelName,
        f.[Description],
        f.IsActive,
        u.UserID AS CreatedBy
    FROM t_FuelTypes AS f
    JOIN t_Users AS u ON u.Id = f.CreatedBy

END



");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_FuelTypes");
    }
};
