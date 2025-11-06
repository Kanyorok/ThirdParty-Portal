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
        DB::unprepared("--R_InsuranceProductRiders '10/10/2024','10/10/2025'

CREATE        PROC [dbo].[R_InsuranceProductRiders] 

@StartDate DateTime ,
@EndDate DateTime

AS
BEGIN
    SET NOCOUNT ON;
Create table #R_InsuranceProductRiders(
  InsuranceProviderId Nvarchar(100),
  Product Nvarchar(100),
  RiderName  Nvarchar(100),
  Description Nvarchar(100),
  AdditionalPremium Money,
  CreatedOn DateTime



)
Insert Into #R_InsuranceProductRiders
Select

  IPS.Name as InsurenceProvider,
  IP.Name  as InsuranceProduct,
  BC.RiderName,
  BC.Description,
  BC.AdditionalPremium,
  BC.CreatedOn

 

from  t_InsuranceProductRiders BC  inner join  t_InsuranceProducts IP ON IP.ID= BC.Product

Inner join t_InsuranceProviders IPS ON IPS.ID= BC.InsuranceProviderId

Where BC.createdon between @StartDate and @EndDate

Select * from #R_InsuranceProductRiders

Drop Table #R_InsuranceProductRiders
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_InsuranceProductRiders");
    }
};
