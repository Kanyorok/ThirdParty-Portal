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

CREATE      PROC [dbo].[R_BankAssuranceCustomers] 

@StartDate DateTime ,
@EndDate DateTime

AS
BEGIN
    SET NOCOUNT ON;
Create table #BankAssuranceCustomers (
  Fullname Nvarchar(100),
  NationId Nvarchar(100),
  KraPin  Nvarchar(100),
  DateOfBirth Date,
  Gender Nvarchar(100),
  PhoneNumber Nvarchar(100),
  EmailAddress Nvarchar(100)


)
Insert Into #BankAssuranceCustomers
Select

  BC.Fullname,
  BC.NationalID,
  BC.KraPin,
  BC.DateOfBirth,
  BC.Gender,
  BC.PhoneNumber,
  BC.Email

from t_BancassuranceCustomers BC 

Where BC.createdon between @StartDate and @EndDate

Select * from #BankAssuranceCustomers

Drop Table #BankAssuranceCustomers
END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS R_BankAssuranceCustomers");
    }
};
