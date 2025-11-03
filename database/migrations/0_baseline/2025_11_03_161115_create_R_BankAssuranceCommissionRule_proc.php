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
        DB::unprepared(" --R_BankAssuranceCommissionRule '10/10/2024','10/10/2025'

Create          PROC [dbo].[R_BankAssuranceCommissionRule] 

@StartDate DateTime ,
@EndDate DateTime

AS
BEGIN
    SET NOCOUNT ON;

	Create Table #R_BankAssuranceCommissionRule(
	 RuleName Nvarchar(200),
	Name Nvarchar(200),
	 PolicyTypeId Nvarchar(200),
	 CommissionRate INT,
	 CreatedOn DateTime
	 --IsActive Nvarchar(200)
		
	)

	Insert into #R_BankAssuranceCommissionRule
	Select
	BC.RuleName,
	IP.Name AS ProductName,
	CD.Description,
	BC.CommissionRate ,
	BC.CreatedOn
	--BC.IsActive

	

	From t_BancassuranceCommissionRules BC 

	Inner join t_InsuranceProducts IP ON IP.ID= BC.ProductId

	--INNER JOIN  t_BancassurancePolicies BP ON BC.PolicyId =BP.ID
	INNER JOIN t_CodeDetails CD  ON CD.ID= BC.PolicyTypeId 
--LEFT JOIN  t_CodeDetails CDS
--            ON CDS.ID = BC.Status
	Where BC.CreatedOn between @StartDate and @EndDate 

	--UPDATE #R_BankAssuranceClaims SET STATUS = (Select description from t_CodeDetails where t_CodeDetails
	--.id = #R_BankAssuranceClaims.Status)
	
	--CD.description WHERE 
	--CD.ID =#R_BankAssuranceClaims.STATUS

	Select * from #R_BankAssuranceCommissionRule

	Drop table #R_BankAssuranceCommissionRule

	END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BankAssuranceCommissionRule");
    }
};
