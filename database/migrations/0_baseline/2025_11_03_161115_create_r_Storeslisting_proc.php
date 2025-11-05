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
        DB::unprepared("CREATE   PROC [dbo].[r_StoresListing] @BranchID VARCHAR(300) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #Storeslisting
    (
        StoreID   VARCHAR(200),
        StoreName VARCHAR(200),
        BranchID  VARCHAR(300),
        CreatedBy VARCHAR(300),
        CreatedOn DATE
    );

    INSERT INTO #Storeslisting
    (StoreID,
     StoreName,
     BranchID,
     CreatedBy,
     CreatedOn)
    SELECT S.StoreID,
           S.StoreName,
           B.Name AS BranchID,
           U.Name AS CreatedBy,
           S.CreatedOn
    FROM t_stores S
             JOIN t_branches B ON B.ID = S.BranchID
             JOIN t_users U ON U.ID = S.CreatedBy
    WHERE
       -- (@BranchID IS NULL OR @BranchID = 'ALL' OR B.Name = @BranchID)
        @BranchID IS NULL
       OR @BranchID = 'ALL'
       OR B.Name IN (SELECT value
                     FROM STRING_SPLIT(@BranchID, ','))


    SELECT * FROM #Storeslisting

    DROP TABLE #Storeslisting
END
--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_Storeslisting");
    }
};
