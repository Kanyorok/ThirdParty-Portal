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
        DB::unprepared("--R_BankAssuranceCommission '10/10/2024','10/10/2025'

Create        PROC [dbo].[R_BankAssuranceCommission] 

@StartDate DateTime ,
@EndDate DateTime

AS
BEGIN
    SET NOCOUNT ON;
Create table #R_BankAssuranceCommission (
  PayoutReference Nvarchar(100),
  PaymentMode Nvarchar(100),
  PaidAmount  Nvarchar(100),
  PaymentDate Date,
 -- PaidBy Nvarchar(100),
  PolicyId Nvarchar(100)



)
Insert Into #R_BankAssuranceCommission
Select

  BC.PayoutReference,
  cd.Description AS PaymentMode,
  BC.PaidAmount,
  BC.PaymentDate,
 -- BC.PaidBy,
  BP.PolicyNumber
 

from  t_BancassuranceCommissionPayouts BC  inner join t_BancassurancePolicies BP

ON BP.Id = BC.PolicyId inner join t_CodeDetails CD ON CD.ID = BC.PaymentMode

Where BC.createdon between @StartDate and @EndDate

Select * from #R_BankAssuranceCommission

Drop Table #R_BankAssuranceCommission
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BankAssuranceCommission");
    }
};
