CREATE OR ALTER PROC [dbo].[r_LegalCases]
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

--EXEC r_LegalCases