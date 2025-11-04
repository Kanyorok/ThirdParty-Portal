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
        DB::unprepared("CREATE    PROCEDURE [dbo].[r_SupplierListing]
AS
BEGIN

    CREATE TABLE #SupplierListing
    (
        Id           nvarchar(50),
        SupplierName varchar(100),
        ContactEmail varchar(150),
        ContactPhone varchar(25),
        Address      
varchar(100),
        CategoryId   varChar(200),
        CreatedBy    varChar(20),
        ModifiedBy   varchar(50),
        CreatedOn    date
    )
    INSERT INTO #SupplierListing
    select S.Id,
           TH.ThirdPartyName,
            TH.Email,
			TH.Phone,
           TH.PhysicalAddress,
           I.Name as CategoryId,
           U.Name as CreatedBy,
           U.Name as ModifiedBy,
           S.CreatedOn

    from t_Suppliers S
             JOIN t_Users U ON U.Id = S.CreatedBy
			 join t_ThirdParties TH ON TH.Id=S.Id
             
JOIN t_ItemCategories I ON I.ID = S.CategoryId

    select * from #SupplierListing

END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_SupplierListing");
    }
};
