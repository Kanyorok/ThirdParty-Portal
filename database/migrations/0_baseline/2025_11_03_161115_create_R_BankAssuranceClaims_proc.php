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
        DB::unprepared(" --R_BankAssuranceClaims '10/10/2024','10/10/2025'

CREATE        PROC [dbo].[R_BankAssuranceClaims] 

@StartDate DateTime ,
@EndDate DateTime

AS
BEGIN
    SET NOCOUNT ON;

	Create Table #R_BankAssuranceClaims(
	 ClaimDate DateTime,
	description Nvarchar(200),
	 ClaimReason Nvarchar(200),
	 ClaimAmount Money,
	 Status Nvarchar(200),
	 PolicyNumber Nvarchar(200)
		
	)

	Insert into #R_BankAssuranceClaims
	Select
	BC.ClaimDate,
	CD.description AS ClaimType,
	BC.ClaimReason,
	BC.ClaimAmount,
	BC.Status ,
	BP.PolicyNumber
	

	From [t_BancassuranceClaims ] BC INNER JOIN  t_BancassurancePolicies BP ON BC.PolicyId =BP.ID
	INNER JOIN t_CodeDetails CD  ON CD.ID= BC.ClaimType 
--LEFT JOIN  t_CodeDetails CDS
--            ON CDS.ID = BC.Status
	Where BC.CreatedOn between @StartDate and @EndDate 

	UPDATE #R_BankAssuranceClaims SET STATUS = (Select description from t_CodeDetails where t_CodeDetails
	.id = #R_BankAssuranceClaims.Status)
	
	--CD.description WHERE 
	--CD.ID =#R_BankAssuranceClaims.STATUS

	Select * from #R_BankAssuranceClaims

	Drop table #R_BankAssuranceClaims

	END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BankAssuranceClaims");
    }
};
