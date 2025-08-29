Create OR ALTER procedure [dbo].[r_PropertyMaintenaceCompletion] @FromDate datetime =null,
                                                                 @ToDate datetime=null,
                                                                 @Status varchar(100)=null
As
Begin
    Create table #propertymaintenacecompletion
    (
        RequestNumber   varchar(200),
        CompletionDate  date,
        WorkDoneSummary varchar(200),
        PartsUsed       varchar(100),
        Cost            money,
        Status          varchar(100),
        CreatedBy       varchar(100),
        CreatedDate     Date

    )
    Insert Into #propertymaintenacecompletion
    Select m.RequestNumber as RequestNumber,
           w.CompletionDate,
           w.WorkDoneSummary,
           w.PartsUsed,
           w.Cost,
           c.Description   as FinalStatus,
           u.name          as CreatedBy,
           w.CreatedOn

    from t_WorkCompletion w
             Join t_MaintenanceRequest m on m.id = w.RequestNumber
             Join t_CodeDetails c on c.id = w.FinalStatus
             Join t_users u on u.id = w.createdby
    Where (@FromDate IS NULL OR w.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR w.CreatedOn < DATEADD(DAY, 1, @ToDate))

      AND (
        @Status IS NULL OR
        @Status = 'ALL' OR
        C.Description IN (SELECT value FROM STRING_SPLIT(@Status, ','))
        );

    Select * from #propertymaintenacecompletion;
END;


--GO
--EXEC r_propertymaintenacecompletion


--GO
