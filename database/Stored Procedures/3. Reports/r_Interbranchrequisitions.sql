CREATE OR ALTER PROC [dbo].[r_Interbranchrequisitions] @FromDate SMALLDATETIME = NULL,
                                                       @ToDate SMALLDATETIME = NULL
AS
BEGIN
    SET NOCOUNT ON;

    CREATE TABLE #Interbranchrequisitions
    (
        RequisitionNumber VARCHAR(200),
        FromBranch        VARCHAR(200),
        RequestingBranch  VARCHAR(200),
        Status            VARCHAR(100),
        SubmittedBy       VARCHAR(200),
        DateCreated       DATE
    );

    INSERT INTO #Interbranchrequisitions (RequisitionNumber,
                                          FromBranch,
                                          RequestingBranch,
                                          Status,
                                          SubmittedBy,
                                          DateCreated)
    SELECT IR.ReqNo,
           BR1.Name      AS FromBranch,
           BR2.Name      AS RequestingBranch,
           C.Description AS Status,
           U.Name        AS SubmittedBy,
           IR.CreatedOn
    FROM t_InterBranchRequisition IR
             JOIN t_branches BR1 ON BR1.ID = IR.FromBranch
             JOIN t_branches BR2 ON BR2.ID = IR.ToBranch
             JOIN t_CodeDetails C ON C.Value = IR.Status -- << Adjust if column is different
             JOIN t_Users U ON U.ID = IR.CreatedBy
    WHERE (@FromDate IS NULL OR IR.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR IR.CreatedOn < DATEADD(DAY, 1, @ToDate));

    SELECT * FROM #Interbranchrequisitions;

    DROP TABLE #Interbranchrequisitions;
END
GO
