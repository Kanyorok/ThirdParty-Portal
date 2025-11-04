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
        DB::unprepared("create   procedure r_LegalCases
as
begin
 
create table #LegalCases
	(
	CaseTitle			varchar(200),
	CourtName			varchar(200),
	FilingDate     Date,
	OpposingParty  varchar(200),
	Status   varchar(200),
	CreatedBy		varchar(100),
	CreatedDate		Date,
	);
 
	insert into #LegalCases
	select 
	L.CaseTitle,
	L.CourtName,
	L.FilingDate,
	L.OpposingParty,
	L.Status,
	U.Name as [CreatedBy],
	L.CreatedOn
	
 
	from t_LegalCases L

	JOIN t_users U ON U.ID = L.CreatedBy
 
	select * from #LegalCases
	END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_LegalCases");
    }
};
