CREATE OR ALTER PROCEDURE p_ProcessWorkflowStages
AS
BEGIN
    SET NOCOUNT ON;
 
    DECLARE @SystemUserId BIGINT = 0; -- Replace with your actual system user ID
 
    DECLARE @SubmittedStatusId BIGINT = (SELECT TOP 1 ID FROM t_CodeDetails WHERE Value = 'SUBMITTED' AND DeletedOn IS NULL);
    DECLARE @ApprovedStatusId BIGINT = (SELECT TOP 1 ID FROM t_CodeDetails WHERE Value = 'APPROVED' AND DeletedOn IS NULL);
    DECLARE @RejectedStatusId BIGINT = (SELECT TOP 1 ID FROM t_CodeDetails WHERE Value = 'REJECTED' AND DeletedOn IS NULL);
 
BEGIN TRY
    BEGIN TRANSACTION;
 
    -- 1. PROCESS NEW SUBMISSIONS (INITIAL STAGE)
    INSERT INTO t_WorkFlowPending (
        Source, SourceID, Stage, UserId,
        CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
    )
    SELECT
        h.Source,
        h.SourceID,
        ws.ID,
        @SystemUserId, -- No user in workflow limits, just system user
        h.CreatedBy,
        GETDATE(),
        h.CreatedBy,
        GETDATE()
    FROM t_WorkFlowHistory h
        INNER JOIN t_WorkFlows wf ON h.Source = wf.Source AND wf.DeletedOn IS NULL
        INNER JOIN t_WorkFlowStages ws ON
            wf.ID = ws.WorkFlowId AND
            ws.[Order] = 1 AND
            ws.DeletedOn IS NULL
    WHERE h.StatusId = @SubmittedStatusId
      AND h.DeletedOn IS NULL
      AND NOT EXISTS (
          SELECT 1 FROM t_WorkFlowPending p
          WHERE p.Source = h.Source
            AND p.SourceID = h.SourceID
            AND p.DeletedOn IS NULL
      );
 
    -- 2. PROCESS COMPLETED APPROVALS (MOVE TO NEXT STAGE)
    -- Mark completed approvals as deleted
    UPDATE p
    SET
        p.DeletedOn = GETDATE(),
        p.DeletedBy = @SystemUserId,
        p.ModifiedBy = @SystemUserId,
        p.ModifiedOn = GETDATE()
    FROM t_WorkFlowPending p
        INNER JOIN t_WorkFlowHistory h ON
            p.Source = h.Source AND
            p.SourceID = h.SourceID AND
            h.DeletedOn IS NULL
    WHERE h.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
      AND p.DeletedOn IS NULL;
 
    -- Create new pending approvals for next stage (only for approved items)
    INSERT INTO t_WorkFlowPending (
        Source, SourceID, Stage, UserId,
        CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
    )
    SELECT
        h.Source,
        h.SourceID,
        nextWs.ID,
        @SystemUserId, -- No user in workflow limits, just system user
        @SystemUserId,
        GETDATE(),
        @SystemUserId,
        GETDATE()
    FROM t_WorkFlowHistory h
        INNER JOIN t_WorkFlows wf ON h.Source = wf.Source AND wf.DeletedOn IS NULL
        INNER JOIN t_WorkFlowStages currentWs ON
            wf.ID = currentWs.WorkFlowId AND
            currentWs.DeletedOn IS NULL
        INNER JOIN t_WorkFlowPending p ON
            p.Source = h.Source AND
            p.SourceID = h.SourceID AND
            p.Stage = currentWs.ID AND
            p.DeletedOn IS NULL
        INNER JOIN t_WorkFlowStages nextWs ON
            wf.ID = nextWs.WorkFlowId AND
            nextWs.[Order] = currentWs.[Order] + 1 AND
            nextWs.DeletedOn IS NULL
    WHERE h.StatusId = @ApprovedStatusId
      AND h.DeletedOn IS NULL
      AND NOT EXISTS (
          SELECT 1 FROM t_WorkFlowPending np
          WHERE np.Source = h.Source
            AND np.SourceID = h.SourceID
            AND np.Stage = nextWs.ID
            AND np.DeletedOn IS NULL
      );
 
    -- 3. FINAL VALIDATION - remove orphaned pending approvals
    UPDATE p
    SET
        p.DeletedOn = GETDATE(),
        p.DeletedBy = @SystemUserId,
        p.ModifiedBy = @SystemUserId,
        p.ModifiedOn = GETDATE()
    FROM t_WorkFlowPending p
    WHERE p.DeletedOn IS NULL
      AND NOT EXISTS (
          SELECT 1 FROM t_WorkFlowHistory h
          WHERE h.Source = p.Source
            AND h.SourceID = p.SourceID
            AND h.DeletedOn IS NULL
      );
 
    COMMIT TRANSACTION;
END TRY
BEGIN CATCH
    IF @@TRANCOUNT > 0
        ROLLBACK TRANSACTION;
 
    DECLARE @ErrorMessage NVARCHAR(4000) = ERROR_MESSAGE();
    DECLARE @ErrorSeverity INT = ERROR_SEVERITY();
    DECLARE @ErrorState INT = ERROR_STATE();
 
    RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
END CATCH
END