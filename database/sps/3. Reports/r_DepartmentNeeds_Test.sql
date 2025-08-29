CREATE OR ALTER PROC [dbo].[r_DepartmentNeeds_Test] @p_BranchName VARCHAR(100) = NULL,
                                                    @p_DepartmentName VARCHAR(100) = NULL,
                                                    @p_Status VARCHAR(50) = NULL,
                                                    @p_MinPriorityLevel INT = NULL,
                                                    @p_RecordCount INT = 0 OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @ReturnCode INT=0;

    BEGIN TRY
        CREATE TABLE #DepartmentalNeeds
        (
            NeedID            INT,
            ItemName          VARCHAR(50),
            DepartmentName    VARCHAR(100),
            BranchName        VARCHAR(100),
            RequestedQty      DECIMAL(18, 2),
            EstimatedUnitCost DECIMAL(18, 2),
            TotalCost         DECIMAL(38, 2),
            Status            VARCHAR(50),
            PriorityLevel     INT,
            Justification     VARCHAR(150),
            CreatedOn         DATE,
            CreatedBy         VARCHAR(50)
        )
        INSERT INTO #DepartmentalNeeds(NeedID,
                                       ItemName,
                                       DepartmentName,
                                       BranchName,
                                       RequestedQty,
                                       EstimatedUnitCost,
                                       TotalCost,
                                       Status,
                                       PriorityLevel,
                                       Justification,
                                       CreatedOn,
                                       CreatedBy)
        SELECT DN.NeedID,
               I.ItemName,
               D.Name                                   as DepartmentName,
               B.Name                                   as BranchName,
               DN.RequestedQty,
               DN.EstimatedUnitCost                     AS UnitCost,
               (DN.RequestedQty * DN.EstimatedUnitCost) AS TotalCost,
               CD.Description                           AS Status,
               DN.PriorityLevel,
               DN.Justification,
               DN.CreatedOn,
               U.Name                                   AS CreatedBy
        FROM dbo.t_DepartmentNeeds DN
                 JOIN
             dbo.t_Users U ON U.Id = DN.CreatedBy
                 JOIN
             dbo.t_Departments D ON D.Id = DN.DepartmentID
                 JOIN
             dbo.t_Branches B ON B.Id = DN.BranchID
                 JOIN
             dbo.t_Items I ON I.Id = DN.ItemID
                 JOIN
             dbo.t_CodeDetails CD ON CD.CodeId = DN.Status

        WHERE (B.Name = @p_BranchName OR @p_BranchName IS NULL)
          AND (D.Name = @p_DepartmentName OR @p_DepartmentName IS NULL)
          AND (CD.Description = @p_Status OR @p_Status IS NULL)
          AND (DN.PriorityLevel >= @p_MinPriorityLevel OR @p_MinPriorityLevel IS NULL);

        SELECT @p_RecordCount = COUNT(*) FROM #DepartmentalNeeds;

        SELECT *
        FROM #DepartmentalNeeds;

    END TRY
    BEGIN CATCH
        SET @ReturnCode = ERROR_NUMBER();
        PRINT 'Error Number: ' + CAST(ERROR_NUMBER() AS VARCHAR(10));
        PRINT 'Error Severity: ' + CAST(ERROR_SEVERITY() AS VARCHAR(10));
        PRINT 'Error State: ' + CAST(ERROR_STATE() AS VARCHAR(10));
        PRINT 'Error Procedure: ' + ERROR_PROCEDURE();
        PRINT 'Error Line: ' + CAST(ERROR_LINE() AS VARCHAR(10));
        PRINT 'Error Message: ' + ERROR_MESSAGE();

        THROW;

        SET @p_RecordCount = -1;
        RETURN @ReturnCode;
    END CATCH;
    IF OBJECT_ID('tempdb..#DepartmentalNeeds') IS NOT NULL
        BEGIN
            DROP TABLE #DepartmentalNeeds;
        END

    RETURN @ReturnCode;
END;


--GO
