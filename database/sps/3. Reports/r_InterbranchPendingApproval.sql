CREATE OR ALTER PROC [dbo].[r_InterbranchPendingApproval] @FromDate DATETIME = NULL,
                                                          @ToDate DATETIME = NULL
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #InterbranchPendingApproval
    (
        ReqNo        VARCHAR(200),
        Item         VARCHAR(300),
        RequestedQty INT,
        Remarks      VARCHAR(200),
        CreatedBy    VARCHAR(200),
        CreatedOn    DATETIME
    );

    INSERT INTO #InterbranchPendingApproval
    SELECT IR.ReqNo,
           I.ItemName,
           IRS.RequestedQty,
           IRS.Remarks,
           U.Name,
           IRS.CreatedOn
    FROM t_InterBranchRequisitionItems IRS
             JOIN t_InterBranchRequisition IR ON IR.ID = IRS.RequisitionId
             JOIN t_Items I ON I.ID = IRS.Item
             JOIN t_users U ON U.ID = IRS.CreatedBy
    WHERE (@FromDate IS NULL OR IRS.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR IRS.CreatedOn < DATEADD(DAY, 1, @ToDate));

    SELECT * FROM #InterbranchPendingApproval;

    DROP TABLE #InterbranchPendingApproval;
END;
--GO
