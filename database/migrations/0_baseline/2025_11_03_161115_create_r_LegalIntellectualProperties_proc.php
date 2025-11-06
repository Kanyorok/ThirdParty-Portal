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
        DB::unprepared("create   procedure r_LegalIntellectualProperties
as
begin
 
create table #LegalIntellectualProperties
	(
	Title			varchar(200),
	IPType			varchar(200),
	Owner  varchar(200),
	Status   varchar(200),
	ExpiryDate Date,
	CreatedBy		varchar(100),
	CreatedDate		Date,
	);
 
	insert into #LegalIntellectualProperties
	select 
	L.Title,
	L.IPType,
	L.Owner,
	L.Status,
	L.ExpiryDate,
	U.Name as [CreatedBy],
	L.CreatedOn
	
 
	from t_LegalIntellectualProperties L

	JOIN t_users U ON U.ID = L.CreatedBy
 
	select * from #LegalIntellectualProperties
	END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_LegalIntellectualProperties");
    }
};
