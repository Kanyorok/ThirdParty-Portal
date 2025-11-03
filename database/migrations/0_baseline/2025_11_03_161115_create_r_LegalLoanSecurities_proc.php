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
        DB::unprepared("create   procedure r_LegalLoanSecurities
as
begin
 
create table #LegalLoanSecurities
	(
	SecurityType			varchar(200),
	OwnerName			varchar(200),
	LoanAccountNumber		varchar(200),
	Value     Int,
	Institution  varchar(200),
	CreatedBy		varchar(100),
	CreatedDate		Date,
	);
 
	insert into #LegalLoanSecurities
	select 
	L.SecurityType,
	L.OwnerName,
	L.LoanAccountNumber,
	L.Value,
	L.Institution,
	U.Name as [CreatedBy],
	L.CreatedOn
	
 
	from t_LegalLoanSecurities L

	JOIN t_users U ON U.ID = L.CreatedBy
 
	select * from #LegalLoanSecurities
	END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_LegalLoanSecurities");
    }
};
