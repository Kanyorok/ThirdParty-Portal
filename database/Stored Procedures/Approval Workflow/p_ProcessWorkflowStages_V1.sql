CREATE OR ALTER PROCEDURE p_ProcessWorkflowStages
    AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @SystemUserId BIGINT = 0; -- Replace with your actual system user ID
    DECLARE @SubmittedStatusId BIGINT = (SELECT Id FROM t_CodeDetails WHERE Code = 'SUBMITTED');
    DECLARE @ApprovedStatusId BIGINT = (SELECT Id FROM t_CodeDetails WHERE Code = 'APPROVED');
    DECLARE @RejectedStatusId BIGINT = (SELECT Id FROM t_CodeDetails WHERE Code = 'REJECTED');

BEGIN TRY
BEGIN TRANSACTION;

        -- =============================================
        -- 1. PROCESS NEW SUBMISSIONS (INITIAL STAGE)
        -- =============================================
INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId,
    CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
SELECT
    h.Source,
    h.SourceID,
    ws.Id,
    COALESCE(wl.UserId, @SystemUserId), -- Fallback to system user if no approver specified
    h.CreatedBy,
    GETDATE(),
    h.CreatedBy,
    GETDATE()
FROM t_WorkFlowHistory h
         INNER JOIN t_WorkFlows wf ON h.Source = wf.Source AND wf.DeletedOn IS NULL
         INNER JOIN t_WorkFlowStages ws ON
    wf.Id = ws.WorkFlowId AND
    ws.[Order] = 1 AND -- First stage
    ws.DeletedOn IS NULL
    LEFT JOIN t_WorkFlowLimits wl ON
    ws.WorkFlowLimitId = wl.Id AND
    wl.DeletedOn IS NULL
WHERE h.StatusId = @SubmittedStatusId
  AND h.DeletedOn IS NULL
  AND NOT EXISTS (
    SELECT 1
    FROM t_WorkFlowPending p
    WHERE p.Source = h.Source
  AND p.SourceID = h.SourceID
  AND p.DeletedOn IS NULL
    );

-- =============================================
-- 2. PROCESS COMPLETED APPROVALS (MOVE TO NEXT STAGE)
-- =============================================
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
    nextWs.Id,
    COALESCE(nextWl.UserId, @SystemUserId), -- Fallback to system user
    @SystemUserId,
    GETDATE(),
    @SystemUserId,
    GETDATE()
FROM t_WorkFlowHistory h
         INNER JOIN t_WorkFlows wf ON h.Source = wf.Source AND wf.DeletedOn IS NULL
         INNER JOIN t_WorkFlowStages currentWs ON
    wf.Id = currentWs.WorkFlowId AND
    currentWs.DeletedOn IS NULL
         INNER JOIN t_WorkFlowPending p ON
    p.Source = h.Source AND
    p.SourceID = h.SourceID AND
    p.Stage = currentWs.Id AND
    p.DeletedOn IS NULL
         INNER JOIN t_WorkFlowStages nextWs ON
    wf.Id = nextWs.WorkFlowId AND
    nextWs.[Order] = currentWs.[Order] + 1 AND -- Next stage
    nextWs.DeletedOn IS NULL
    LEFT JOIN t_WorkFlowLimits nextWl ON
    nextWs.WorkFlowLimitId = nextWl.Id AND
    nextWl.DeletedOn IS NULL
WHERE h.StatusId = @ApprovedStatusId
  AND h.DeletedOn IS NULL
  AND NOT EXISTS (
    SELECT 1
    FROM t_WorkFlowPending np
    WHERE np.Source = h.Source
  AND np.SourceID = h.SourceID
  AND np.Stage = nextWs.Id
  AND np.DeletedOn IS NULL
    );

-- =============================================
-- 3. FINAL VALIDATION
-- =============================================
-- Ensure we don't have orphaned pending approvals
UPDATE p
SET
    p.DeletedOn = GETDATE(),
    p.DeletedBy = @SystemUserId,
    p.ModifiedBy = @SystemUserId,
    p.ModifiedOn = GETDATE()
    FROM t_WorkFlowPending p
WHERE p.DeletedOn IS NULL
  AND NOT EXISTS (
    SELECT 1
    FROM t_WorkFlowHistory h
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
