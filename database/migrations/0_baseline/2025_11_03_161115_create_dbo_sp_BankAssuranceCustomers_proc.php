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
        DB::unprepared("--sp_BankAssuranceCustomers '10/10/2024','10/10/2025'

CREATE  procedure [dbo.sp_BankAssuranceCustomers]

@StarDate DateTime,
@EndDate DateTime 
AS
BEGIN

    SET NOCOUNT ON;

Create  Table #BankAssuranceCustomers(
Fullname Nvarchar (100),
NationaID Nvarchar (100),
KRAPIN Nvarchar (100),
DateOfBirth Date,
Gender Nvarchar (100),
PhoneNumber Nvarchar (100),
Email Nvarchar (100)


)
Insert into #BankAssuranceCustomers
Select  
Fullname,
NationalID,
KRAPIN,
DateOfBirth,
Gender,
PhoneNumber,
Email

From t_BancassuranceCustomers  
where t_BancassuranceCustomers.CreatedOn between @StarDate and @EndDate

END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS dbo.sp_BankAssuranceCustomers");
    }
};
