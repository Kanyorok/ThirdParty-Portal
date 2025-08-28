CREATE OR ALTER PROCEDURE [dbo].[r_DepartmentNeeds] @FromDate DATETIME = NULL,
                                                    @ToDate DATETIME = NULL,
                                                    @BranchName varchar(100)= null,
                                                    @PriorityLevel varchar(20) = null
AS
BEGIN

    CREATE TABLE #DepartmentNeeds
    (
        NeedID            NVARCHAR(200),
        BranchID          VARCHAR(100),
        Item              VARCHAR(150),
        RequestedQty      VARCHAR(25),
        EstimatedUnitCost MONEY,
        status            VARCHAR(20),
        CreatedBy         VARCHAR(50),
        CreatedOn         DATE,
        Justification     VARCHAR(200)

    )


    INSERT INTO #DepartmentNeeds
    SELECT DN.NeedID,
           B.Name         as BranchID,
           IT.ItemName,
           DN.RequestedQty,
           DN.EstimatedUnitCost,
           CD.Description AS Status,
           U.Name         as CreatedBy,
           DN.CreatedOn,
           DN.Justification
    from t_DepartmentNeeds AS DN
             JOIN t_items AS IT on IT.ID = DN.ItemID
             JOIN t_Branches AS B ON B.Id = DN.BranchID
             JOIN t_Codedetails AS CD ON DN.Status = CD.Value AND CD.CodeID = 'DepartmentNeedsStatus'


             JOIN t_Users U ON U.Id = DN.CreatedBy

    WHERE (@FromDate IS NULL OR DN.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR DN.CreatedOn < DATEADD(DAY, 1, @ToDate))
      AND (@BranchName IS NULL OR @BranchName = 'ALL' OR B.Name IN (SELECT value FROM STRING_SPLIT(@BranchName, ',')))


    SELECT * FROM #DepartmentNeeds
    DROP TABLE #DepartmentNeeds
END

--GO

--EXEC r_DepartmentNeeds


GO
