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
        DB::unprepared("create   procedure r_LegalTemplates
as
begin
 
create table #LegalTemplates
	(
	Title			varchar(200),
	DocumentType			varchar(200),
	--Content			varchar(400),
	Version			varchar(200),
	Status		varchar(200),
	CreatedBy		varchar(100),
	CreatedDate		Date,
	);
 
	insert into #LegalTemplates
	select 
	L.Title,
	L.DocumentType,
	--L.Content,
	L.Version,
	L.Status,
	U.Name as [CreatedBy],
	L.CreatedOn
	
 
	from t_LegalTemplates L

	JOIN t_users U ON U.ID = L.CreatedBy
 
	select * from #LegalTemplates
	END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_LegalTemplates");
    }
};
