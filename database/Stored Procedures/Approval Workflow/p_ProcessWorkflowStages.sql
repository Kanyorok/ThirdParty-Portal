CREATE OR ALTER PROCEDURE p_ProcessWorkflowStages
    AS
BEGIN
    SET NOCOUNT ON;

    -- Process submissions and stage transitions
BEGIN TRY
BEGIN TRANSACTION;

        -- Handle new submissions (create initial pending approvals)
INSERT INTO t_WorkFlowPending (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn)
SELECT
    h.Source, h.SourceID, ws.Id, wl.UserId,
    h.CreatedBy, GETDATE()
FROM t_WorkFlowHistory h
         JOIN t_WorkFlows wf ON h.Source = wf.Source
         JOIN t_WorkFlowStages ws ON wf.Id = ws.WorkFlowID AND ws.[Order] = 1
    JOIN t_WorkFlowLimits wl ON ws.Id = wl.StageID
WHERE h.ActionType = 'submit'
  AND NOT EXISTS (
    SELECT 1 FROM t_WorkFlowPending p
    WHERE p.Source = h.Source AND p.SourceID = h.SourceID
    )
  AND wl.IsActive = 1
  AND (wf.ApprovalType != 'AMT' OR wl.MaxAmount >= h.Amount);

-- Handle stage transitions for completed stages
INSERT INTO t_WorkFlowPending (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn)
SELECT
    p.Source, p.SourceID, nextWs.Id, wl.UserId,
    'System', GETDATE()
FROM (
         SELECT Source, SourceID, Stage, COUNT(*) AS ApprovalsNeeded
         FROM t_WorkFlowPending
         WHERE DeletedOn IS NULL
         GROUP BY Source, SourceID, Stage
     ) p
         JOIN t_WorkFlowStages currentWs ON p.Stage = currentWs.Id
         JOIN t_WorkFlows wf ON currentWs.WorkFlowID = wf.Id
         JOIN t_WorkFlowStages nextWs ON wf.Id = nextWs.WorkFlowID AND nextWs.[Order] = currentWs.[Order] + 1
    JOIN t_WorkFlowLimits wl ON nextWs.Id = wl.StageID
WHERE NOT EXISTS (
    SELECT 1 FROM t_WorkFlowPending
    WHERE Source = p.Source AND SourceID = p.SourceID AND DeletedOn IS NULL
    )
  AND wl.IsActive = 1;

COMMIT TRANSACTION;
END TRY
BEGIN CATCH
IF @@TRANCOUNT > 0 ROLLBACK TRANSACTION;
        THROW;
END CATCH
END
