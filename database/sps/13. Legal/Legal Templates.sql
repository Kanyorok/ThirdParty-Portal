CREATE OR ALTER PROC [dbo].[r_LegalTemplates]
as
begin
 
create table #LegalTemplates
	(
	Title			varchar(200),
	DocumentType			varchar(200),
	Version			varchar(200),
	Status		varchar(200),
	CreatedBy		varchar(100),
	CreatedDate		Date,
	);
 
	insert into #LegalTemplates
	select 
	L.Title,
	L.DocumentType,
	L.Version,
	L.Status,
	U.Name as [CreatedBy],
	L.CreatedOn
	
 
	from t_LegalTemplates L

	JOIN t_users U ON U.ID = L.CreatedBy
 
	select * from #LegalTemplates
	END
