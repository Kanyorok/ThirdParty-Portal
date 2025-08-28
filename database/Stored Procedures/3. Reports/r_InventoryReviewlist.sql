Create OR ALTER proc [dbo].[R_InventoryReviewlist] @FromDate datetime = null,
                                                   @ToDate datetime = null,
                                                   @BranchID Varchar(100)=null
as
begin
    Create table #InventoryReviewlist
    (
        InventoryholdID Nvarchar(200),
        Item            Varchar(100),
        FromBranch      Varchar(100),
        Store           Varchar(100),
        Quantity        Int,
        Reason          Varchar(200),
        Source          Varchar(100),
        Status          Varchar(100),
        Remarks         Varchar(200),
        CreatedBy       Varchar(100),
        CreatedOn       Date

    )

    Insert into #InventoryReviewList
    SELECT I.InventoryHoldID,
           IT.ItemName    as ItemID,
           B.Name         as BranchID,
           I.Store,
           I.Quantity,
           C.Description  as Reason,
           CT.Description as Source,
           CD.Description AS Status,
           I.Remarks,
           U.Name         as CreatedBy,
           I.CreatedOn
    from t_InventoryHold I
             Join t_users U on U.ID = I.CreatedBy
             Join t_Items IT on IT.ID = I.ItemID
             Join t_branches B on B.ID = I.BranchID
             Join t_codedetails C on C.ID = I.Reason
             Join t_codedetails CT on CT.ID = I.Source
             JOIN t_CodeDetails CD ON CD.Value = I.Status

    where (@FromDate IS NULL OR I.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR I.CreatedOn < DATEADD(DAY, 1, @ToDate))

      AND (@BranchID IS NULL OR @BranchID = 'ALL' OR B.Name IN (SELECT value FROM STRING_SPLIT(@BranchID, ',')));

    select * from #InventoryReviewlist
end

GO
