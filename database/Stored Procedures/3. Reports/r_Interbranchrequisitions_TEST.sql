CREATE OR ALTER PROC [dbo].[r_Interbranchrequisitions_TEST] @FromDate DATETIME = NULL,
                                                            @ToDate DATETIME = NULL
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #Interbranchrequisitions
    (
        ReqNo      VARCHAR(200),
        FromBranch VARCHAR(200),
        ToBranch   VARCHAR(200),
        STATUS     VARCHAR(100),
        CreatedBy  VARCHAR(200),
        CreatedOn  DATE
    )

    INSERT INTO #Interbranchrequisitions
    SELECT IR.ReqNo,
           BR1.Name AS FromBranch,
           BR2.Name AS ToBranch,
           IR.Status,
           U.Name   AS CreatedBy,
           IR.CreatedOn
    FROM t_InterBranchRequisition IR
             JOIN t_branches BR1 ON BR1.ID = IR.FromBranch
             JOIN t_branches BR2 ON BR2.ID = IR.ToBranch
             JOIN t_Users U ON U.ID = IR.CreatedBy
    WHERE
        --(@FromDate IS NULL OR CAST(FORMAT(IR.CreatedOn,'dd-MM-yyy') as date) >= @FromDate)
        --AND
        --(@ToDate IS NULL OR CAST(FORMAT(IR.CreatedOn,'dd-MM-yyy') as date) <= @ToDate)
        IR.CreatedOn
            BETWEEN
            @FromDate AND @ToDate + 1

    SELECT * FROM #Interbranchrequisitions

    DROP TABLE #Interbranchrequisitions
END
GO
