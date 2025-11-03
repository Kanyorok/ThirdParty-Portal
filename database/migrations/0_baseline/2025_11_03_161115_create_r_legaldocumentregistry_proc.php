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
        DB::unprepared("create    procedure r_legaldocumentregistry
as
begin
 
create table #legaldocumentregistry
	(
	Title			varchar(200),
	Type			varchar(200),
	Source			varchar(200),
	Review			varchar(200),
	Execution		varchar(200),
	CreatedBy		varchar(100),
	CreatedDate		Date,
	Remarks			varchar(MAX),
	Active			varchar(100)
	)
 
	insert into #legaldocumentregistry
	select 
	L.DocumentTitle,
	L.DocumentType,
	L.SourceModule,
	L.ReviewStatus,
	L.ExecutionStatus,
	U.Name as [CreatedBy],
	L.CreatedOn,
	L.Remarks,
	CASE L.IsActive
	WHEN '1' THEN 'Yes'
	WHEN '0' THEN 'No'
	ELSE 'unknown'
	END AS IsActive
 
	from t_LegalDocuments L
	JOIN t_users U ON U.ID = L.CreatedBy
 
	select * from #legaldocumentregistry
	drop table #legaldocumentregistry
	END
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_legaldocumentregistry");
    }
};
