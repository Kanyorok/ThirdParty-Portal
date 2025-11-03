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
        DB::unprepared("--R_BancassuranceBeneficiaries '10/10/2024','10/10/2025'

CREATE    PROC [dbo].[R_BancassuranceBeneficiaries] 

@StartDate DateTime ,
@EndDate DateTime

AS
BEGIN
    SET NOCOUNT ON;
Create table #R_BancassuranceBeneficiaries(
  Description Nvarchar(100),
  PercentageShare Nvarchar(100),
  CustomerName  Nvarchar(100),
  PolicyNumber Nvarchar(100),
  Email Nvarchar(100),
  FullName nvarchar(200),
  IDNumber nvarchar(200),

)
Insert Into #R_BancassuranceBeneficiaries
Select

  CD.Description,
  BC.PercentageShare,
  BCs.FullName AS CustomerName,
  BCP.PolicyNumber,
  BC.Email,
  BC.FullName,
  BC.IDNumber

 

from  t_BancassuranceBeneficiaries BC  inner join  t_BancassuranceCustomers
BCS ON BCS.ID=  BC.CustomerID
Inner join  t_BancassurancePolicies BCP ON BCP.Id =BC.PolicyID
Inner join t_CodeDetails CD ON CD.ID =BC.Relationship
Where BC.createdon between @StartDate and @EndDate

Select * from #R_BancassuranceBeneficiaries

Drop Table #R_BancassuranceBeneficiaries
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BancassuranceBeneficiaries");
    }
};
