CREATE OR ALTER   FUNCTION [dbo].[f_getPermissionForAmount]
(
    @StageID BIGINT,
    @Amount  DECIMAL(20,4)
)
RETURNS TABLE
AS
RETURN
(
    WITH Limits AS (
        SELECT PermissionId, MaxAmount,
               ROW_NUMBER() OVER (ORDER BY MaxAmount ASC) as rn
        FROM t_WorkFlowLimits
        WHERE WorkFlowStageId = @StageID
          AND DeletedOn IS NULL
          AND MaxAmount IS NOT NULL
    ),
    -- Find the lowest limit that covers the amount
    CoveringLimit AS (
        SELECT TOP 1 PermissionId, MaxAmount
        FROM Limits
        WHERE @Amount <= MaxAmount
        ORDER BY MaxAmount ASC
    ),
    -- Fallback: highest limit if amount exceeds all limits
    HighestLimit AS (
        SELECT TOP 1 PermissionId, MaxAmount
        FROM Limits
        ORDER BY MaxAmount DESC
    ),
    -- Default: stage's own permission if no limits defined
    StageDefault AS (
        SELECT PermissionId, CAST(NULL AS DECIMAL(20,4)) as MaxAmount
        FROM t_WorkFlowStages
        WHERE Id = @StageID AND DeletedOn IS NULL
    )
    SELECT PermissionId, MaxAmount, 'COVERING' as LimitType
    FROM CoveringLimit
    UNION ALL
    SELECT PermissionId, MaxAmount, 'EXCEEDED' as LimitType
    FROM HighestLimit
    WHERE NOT EXISTS (SELECT 1 FROM CoveringLimit)
      AND EXISTS (SELECT 1 FROM Limits)
    UNION ALL
    SELECT PermissionId, MaxAmount, 'DEFAULT' as LimitType
    FROM StageDefault
    WHERE NOT EXISTS (SELECT 1 FROM Limits)
);
