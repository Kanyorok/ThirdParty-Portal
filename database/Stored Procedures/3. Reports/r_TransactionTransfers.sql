CREATE OR ALTER PROC [dbo].[r_TransactionTransfers] @FromDate DATE = NULL,
                                                    @ToDate DATE = NULL,
                                                    @FromBranch VARCHAR(200) = NULL,
                                                    @ToBranch VARCHAR(200) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #TransactionTransfers
    (
        TransferID      NVARCHAR(200),
        RequisitionId   NVARCHAR(200),
        TransferDate    DATE,
        TransferredBy   NVARCHAR(200),
        Status          NVARCHAR(200),
        FromBranch      NVARCHAR(200),
        ToBranch        NVARCHAR(200),
        RequisitionType NVARCHAR(200),
        CreatedBy       NVARCHAR(200),
        CreatedOn       DATE
    );

    INSERT INTO #TransactionTransfers
    SELECT T.TransferID,
           IR.ReqNo      AS RequisitionId,
           T.TransferDate,
           TU.Name       AS TransferredBy,
           C.Description AS Status,
           BR.Name       AS FromBranch,
           BR2.Name      AS ToBranch,
           T.RequisitionType,
           CU.Name       AS CreatedBy,
           T.CreatedOn
    FROM t_Transfers T
             JOIN t_interbranchrequisition IR ON IR.ID = T.RequisitionId
             JOIN t_Branches BR ON BR.Id = T.FromBranch
             JOIN t_Branches BR2 ON BR2.Id = T.ToBranch
             JOIN t_users TU ON TU.ID = T.TransferredBy
             JOIN t_users CU ON CU.ID = T.CreatedBy
             JOIN t_CodeDetails C ON C.Value = T.Status
    WHERE (@FromDate IS NULL OR T.TransferDate >= @FromDate)
      AND (@ToDate IS NULL OR T.TransferDate < DATEADD(DAY, 1, @ToDate))
      AND (@FromBranch IS NULL OR @FromBranch = 'ALL' OR @FromBranch = BR.Name)
      AND (@ToBranch IS NULL OR @ToBranch = 'ALL' OR @ToBranch = BR2.Name)
      AND C.CodeID = 'RequisitionStatus';

    SELECT * FROM #TransactionTransfers

    DROP TABLE #TransactionTransfers
END


GO
