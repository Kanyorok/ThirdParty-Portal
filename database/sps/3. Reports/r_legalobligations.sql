Create OR ALTER procedure [dbo].[r_legalobligations]
as
begin
    create table #legalobligations
    (
        Obligation   Varchar(200),
        Source       Varchar(100),
        DueDate      Date,
        Status       Varchar(100),
        Descriptions Varchar(255),
        Active       Varchar(100),
        Assignedto   Varchar(100),
        ScheduleID   Varchar(100),
        CreatedDate  Date
    )
    insert into #legalobligations
    select O.Title,
           O.SourceType,
           O.DueDate,
           O.Status,
           O.Description,
           CASE
               WHEN O.IsActive = '1' THEN 'Yes'
               WHEN O.IsActive = '0' THEN 'No'
               ELSE CAST(O.IsActive AS VARCHAR)
               END AS IsActive,
           U.Name  as [AssignedTo],
           O.ScheduledID,
           O.CreatedOn

    From t_LegalObligations O
             Join t_users U on U.ID = O.AssignedTo

    select * from #legalobligations
END
--GO
--EXEC r_legalobligations


--select *  From t_LegalObligations
--GO
