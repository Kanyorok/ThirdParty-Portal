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
        DB::unprepared("create   procedure r_LegalClauses
as
begin
 
create table #LegalClauses
	(
	Title			varchar(200),
	Type			varchar(200),
	--Content			varchar(400),
	Version			varchar(200),
	Standard		varchar(200),
	CreatedBy		varchar(100),
	CreatedDate		Date,
	);
 
	insert into #LegalClauses
	select 
	L.Title,
	L.ClauseType,
	--L.Content,
	L.Version,
	L.IsStandard,
	U.Name as [CreatedBy],
	L.CreatedOn
	
 
	from t_LegalClauses L

	JOIN t_users U ON U.ID = L.CreatedBy
 
	select * from #LegalClauses
	END
	--GO
	--EXEC r_LegalClauses");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_LegalClauses");
    }
};
