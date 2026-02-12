CREATE OR ALTER VIEW dbo.v_WorkflowEscalationStatus
AS
SELECT
    wp.Id,
    wp.Source,
    wp.SourceID,
    wp.Stage,
    wp.UserId as AssignedTo,
    wp.CreatedOn,
    wp.EscalatedOn,
    ws.StageName,
    ws.EscalationLimit,
    DATEDIFF(HOUR, COALESCE(wp.EscalatedOn, wp.CreatedOn), GETDATE()) AS HoursSinceLastAction,
    DATEDIFF(HOUR, COALESCE(wp.EscalatedOn, wp.CreatedOn), GETDATE()) - (ws.EscalationLimit * 24) AS HoursOverdue,
    CASE
        WHEN wp.EscalatedOn IS NULL
             AND DATEDIFF(HOUR, wp.CreatedOn, GETDATE()) > (ws.EscalationLimit * 24)
        THEN 'NEEDS FIRST ESCALATION'
        WHEN wp.EscalatedOn IS NOT NULL
             AND DATEDIFF(HOUR, wp.EscalatedOn, GETDATE()) > (ws.EscalationLimit * 24)
        THEN 'NEEDS RE-ESCALATION'
        WHEN wp.EscalatedOn IS NOT NULL
             AND DATEDIFF(HOUR, wp.EscalatedOn, GETDATE()) <= (ws.EscalationLimit * 24)
        THEN 'RECENTLY ESCALATED'
        ELSE 'WITHIN LIMIT'
    END AS EscalationStatus
FROM dbo.t_WorkFlowPending wp
INNER JOIN dbo.t_WorkFlowStages ws
    ON wp.Stage = ws.Id
WHERE
    wp.DeletedBy IS NULL
    AND wp.DeletedOn IS NULL
    AND ws.DeletedBy IS NULL
    AND ws.DeletedOn IS NULL
    AND ws.EscalationLimit IS NOT NULL;
GO
