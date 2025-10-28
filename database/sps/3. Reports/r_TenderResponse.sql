CREATE OR ALTER PROC [dbo].[r_TenderResponse] @FromDate smalldatetime = null,
                                              @ToDate smalldatetime = null
AS
BEGIN
    CREATE table #TenderResponse
    (
        InvitationID   VARCHAR(200),
        TenderID       VARCHAR(200),
        SupplierID     VARCHAR(200),
        InvitationDate DATE,
        ResponseStatus VARCHAR(200),
        ResponseDate   DATE,
        DeclineReason  VARCHAR(200),
        CreatedBy      VARCHAR(200),
        CreatedOn      DATE

    )
    INSERT INTO #TenderResponse
    SELECT I.InvitationId,
           T.Title        as TenderId,
           S.SupplierName as SupplierId,
           I.InvitationDate,
           I.ResponseStatus,
           I.ResponseDate,
           I.DeclineReason,
           U.Name         as CreatedBy,
           I.CreatedOn
    FROM t_TenderInvitations I

             JOIN t_Tenders T ON T.ID = I.TenderId
             JOIN t_Suppliers S ON S.ID = I.SupplierID
             JOIN t_users U ON U.Id = I.CreatedBy


    WHERE (@FromDate IS NULL OR I.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR I.CreatedOn < DATEADD(DAY, 1, @ToDate))


    Select * from #TenderResponse;

END
--GO
