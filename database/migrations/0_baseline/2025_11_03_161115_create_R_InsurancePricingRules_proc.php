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
        DB::unprepared("--R_InsurancePricingRules '10/10/2024','10/10/2025'

CREATE      PROC [dbo].[R_InsurancePricingRules] 

@StartDate DateTime ,
@EndDate DateTime

AS
BEGIN
    SET NOCOUNT ON;
Create table #R_InsurancePricingRules (
  IPS_Name Nvarchar(100) ,
  IP_Name Nvarchar(100),
  RuleName  Nvarchar(100),
  CoverageAmountMin Float,
  CoverageAmountMax float,
  PremiumRate Float,
  AgeMin INT,
  AgeMax INT,
  CreatedOn DateTime



)
Insert Into #R_InsurancePricingRules
Select

  IPS.Name,
  IP.Name,
  BC.RuleName,
  BC.CoverageAmountMin,
  BC.CoverageAmountMax,
  BC.PremiumRate,
  BC.AgeMin,
  BC.AgeMax,
  BC.CreatedOn
 

from  t_InsurancePricingRules BC  inner join  t_insuranceProducts IPS ON IPS.id =BC.Product
Inner join t_InsuranceProviders IP ON IP.Id =BC.InsuranceProviderId
Where BC.createdon between @StartDate and @EndDate

--UPDATE c
--SET c.ClosedBy = CAST(u.UserID AS nvarchar)
--FROM #R_BancassuranceClaimClosures AS c
--JOIN t_Users AS u
--  ON TRY_CAST(u.UserID AS nvarchar) = c.ClosedBy
--WHERE TRY_CAST(u.UserID AS nvarchar) IS NOT NULL;



--Update #R_BancassuranceClaimClosures set FinalStatus=(
--select Description from t_CodeDetails where #R_BancassuranceClaimClosures.FinalStatus = 
--cast(isnull(t_CodeDetails.ID,0 )as nvarchar))

Select * from #R_InsurancePricingRules

Drop Table #R_InsurancePricingRules
END

--Select * from t_Users where codeID like '%created%'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_InsurancePricingRules");
    }
};
