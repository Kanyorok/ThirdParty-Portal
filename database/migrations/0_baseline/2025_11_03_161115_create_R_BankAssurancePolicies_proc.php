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
        DB::unprepared("--R_BankAssurancePolicies '10/10/2014','10/10/2025'
CREATE        PROC [dbo].[R_BankAssurancePolicies] 

@StartDate DateTime ,
@EndDate DateTime

AS
BEGIN
    SET NOCOUNT ON;

Create table #BankAssurancePolicies (
  policyNumber Nvarchar(100),
  FullName Nvarchar(100),
  Name  Nvarchar(100),
  SumAssured Money,
  premiumAmount Money,
  InsurerID Nvarchar(100)

)
Insert Into #BankAssurancePolicies

Select

  BP.PolicyNumber,
  BC.FullName,
  IP.Name,
  BP.SumAssured,
  BP.premiumAmount,
  IPS.Name 

from t_BancassurancePolicies BP  
INNER JOIN t_BancassuranceCustomers BC ON BC.Id =BP.CustomerID
INNER JOIN t_InsuranceProducts IP ON IP.Id =BP.ProductID
INNER JOIN t_InsuranceProviders IPS ON IPS.id =BP.InsurerID

Where BP.createdon between @StartDate and @EndDate

Select * from #BankAssurancePolicies

Drop Table #BankAssurancePolicies
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BankAssurancePolicies");
    }
};
