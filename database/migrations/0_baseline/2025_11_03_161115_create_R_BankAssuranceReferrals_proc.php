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
        DB::unprepared("

Create        PROC [dbo].[R_BankAssuranceReferrals] 

@StartDate DateTime ,
@EndDate DateTime

AS
BEGIN
    SET NOCOUNT ON;
Create table #BankAssuranceReferrals (
  ClientIDNumber Nvarchar(100),
  ClientName Nvarchar(100),
  ReferralDate  Date,
  ClientPhone NVARCHAR(100),
  PhoneNumber Nvarchar(100),
  ClientEmail Nvarchar(100)


)
Insert Into #BankAssuranceReferrals
Select

  BR.ClientIDNumber,
  BR.ClientName,
  BR.ReferralDate,
  Br.ReferredBy,
  BR.ClientPhone,
  BR.ClientEmail

from t_BancassuranceReferrals BR 

Where BR.createdon between @StartDate and @EndDate

Select * from #BankAssuranceReferrals

Drop Table #BankAssuranceReferrals
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BankAssuranceReferrals");
    }
};
