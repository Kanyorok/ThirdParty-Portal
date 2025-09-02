CREATE OR ALTER PROCEDURE [dbo].[p_ProcessWorkflowStages_redo]
    @SystemUserId NVARCHAR(100),
    @WorkflowStagePermission BIGINT
AS
BEGIN
    SET NOCOUNT ON;

BEGIN TRY
        DECLARE
@CurrentOrder INT,
            @PermissionId BIGINT,
            @StageId BIGINT,
            @Count INT,
            @WorkflowType NVARCHAR(100),
            @Source NVARCHAR(100),
            @SourceId NVARCHAR(100),
            @Now DATETIME = GETDATE(),
            @ApprovedStatusId BIGINT,
            @ActualApprovals INT,
            @RequiredApprovals INT,
            @NextStageId BIGINT,
            @Amount DECIMAL(20,4);

        -- Lookup 'Approved' status ID from code details
SELECT @ApprovedStatusId = ID
FROM t_CodeDetails
WHERE Description IN ('Approved', 'Approval');

IF @ApprovedStatusId IS NULL
BEGIN
            RAISERROR('Approved status not found in t_CodeDetails.', 16, 1);
            RETURN;
END

        -- Get only the latest approved stage per Source + SourceID where pending is soft deleted
        ;WITH LatestApproved AS
                  (
                      SELECT
                          p.Source,
                          p.SourceID,
                          CAST(p.Stage AS BIGINT) AS StageId,
                          ROW_NUMBER() OVER (PARTITION BY p.Source, p.SourceID ORDER BY CAST(p.Stage AS BIGINT) DESC) AS rn
                      FROM t_WorkFlowPending p
                               INNER JOIN t_WorkFlowHistory h
                                          ON p.Source = h.Source
                                              AND p.SourceID = h.SourceID
                                              AND p.Stage = h.Stage
                      WHERE h.StatusId = @ApprovedStatusId
                        AND p.DeletedOn IS NOT NULL
                  )
         SELECT Source, SourceID, StageId
         INTO #ApprovalItems
         FROM LatestApproved
         WHERE rn = 1;

DECLARE approval_cursor CURSOR FOR
SELECT Source, SourceID, StageId FROM #ApprovalItems;

OPEN approval_cursor;
FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId;

WHILE @@FETCH_STATUS = 0
BEGIN
            -- Fetch current stage metadata and permissions
SELECT @CurrentOrder = s.[Order], @PermissionId = s.PermissionId
FROM t_WorkFlowStages s
WHERE s.Id = @StageId AND s.DeletedOn IS NULL;

SELECT @WorkflowType = wt.TypeID, @Count = ISNULL(s.[Count], 0)
FROM t_WorkFlowStages s
         JOIN t_WorkFlowTypes wt ON s.WorkFlowTypeId = wt.Id
WHERE s.Id = @StageId;

-- Count actual distinct user approvals in history for current stage
SELECT @ActualApprovals = COUNT(DISTINCT h.CreatedBy)
FROM t_WorkFlowHistory h
WHERE h.Source = @Source
  AND h.SourceID = @SourceId
  AND h.Stage = CAST(@StageId AS NVARCHAR(50))
  AND h.StatusId = @ApprovedStatusId
  AND h.DeletedOn IS NULL;

-- Determine how many approvals are required based on workflow type
IF @WorkflowType = 'ALL'
BEGIN
SELECT @RequiredApprovals = COUNT(*)
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    EXISTS (
        SELECT 1 FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId
          AND mp.model_type = 'UserID'
          AND mp.model_id = u.Id
    )
        OR EXISTS (
        SELECT 1 FROM t_Roles r
                          JOIN t_ModelRoles mr ON r.id = mr.role_id
                          JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id
          AND mr.model_type = 'UserID'
          AND rp.permission_id = @PermissionId
    )
    );
END
ELSE IF @WorkflowType = 'CNT'
                SET @RequiredApprovals = @Count;
ELSE IF @WorkflowType = 'MAJ'
BEGIN
SELECT @RequiredApprovals = CEILING(COUNT(*) * 1.0 / 2)
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    EXISTS (
        SELECT 1 FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId
          AND mp.model_type = 'UserID'
          AND mp.model_id = u.Id
    )
        OR EXISTS (
        SELECT 1 FROM t_Roles r
                          JOIN t_ModelRoles mr ON r.id = mr.role_id
                          JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id
          AND mr.model_type = 'UserID'
          AND rp.permission_id = @PermissionId
    )
    );
END
ELSE IF @WorkflowType = 'AMT'
                SET @RequiredApprovals = @Count;
ELSE
                SET @RequiredApprovals = 0;

            -- Only proceed if required approvals reached
            IF @ActualApprovals >= @RequiredApprovals AND @RequiredApprovals > 0
BEGIN
                -- Identify next stage in workflow
SELECT TOP 1 @NextStageId = s.Id
FROM t_WorkFlowStages s
WHERE s.[Order] = @CurrentOrder + 1
    AND s.DeletedOn IS NULL;

IF @NextStageId IS NOT NULL
BEGIN
                    -- Get next stage metadata including permission and workflow type
SELECT TOP 1
                        @PermissionId = s.PermissionId,
    @WorkflowType = wt.TypeID,
       @Count = ISNULL(s.[Count], 0),
       @Amount = s.Amount
FROM t_WorkFlowStages s
         JOIN t_WorkFlowTypes wt ON s.WorkFlowTypeId = wt.Id
WHERE s.Id = @NextStageId;

-- Insert pending workflow entries based on workflow type
IF @WorkflowType = 'ALL'
BEGIN
INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
SELECT @Source, @SourceId, CAST(@NextStageId AS NVARCHAR(50)), u.Id,
       @SystemUserId, @Now, @SystemUserId, @Now
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    EXISTS (
        SELECT 1 FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId
          AND mp.model_type = 'UserID'
          AND mp.model_id = u.Id
    )
        OR
    EXISTS (
        SELECT 1 FROM t_Roles r
                          JOIN t_ModelRoles mr ON r.id = mr.role_id
                          JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id
          AND mr.model_type = 'UserID'
          AND rp.permission_id = @PermissionId
    )
    )
  AND NOT EXISTS (
    SELECT 1 FROM t_WorkFlowPending p
    WHERE p.Source = @Source
      AND p.SourceID = @SourceId
      AND p.Stage = CAST(@NextStageId AS NVARCHAR(50))
      AND p.UserId = u.Id
      AND p.DeletedOn IS NULL
);
END
ELSE IF @WorkflowType = 'CNT'
BEGIN
                        DECLARE @AlreadyAssigned INT, @ToInsert INT;

SELECT @AlreadyAssigned = COUNT(*)
FROM t_WorkFlowPending p
WHERE p.Source = @Source
  AND p.SourceID = @SourceId
  AND p.Stage = CAST(@NextStageId AS NVARCHAR(50))
  AND p.DeletedOn IS NULL;

SET @ToInsert = @Count - ISNULL(@AlreadyAssigned, 0);

                        IF @ToInsert > 0
BEGIN
INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
SELECT TOP (@ToInsert) @Source, @SourceId, CAST(@NextStageId AS NVARCHAR(50)), u.Id,
       @SystemUserId, @Now, @SystemUserId, @Now
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    EXISTS (
        SELECT 1 FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId
          AND mp.model_type = 'UserID'
          AND mp.model_id = u.Id
    )
        OR
    EXISTS (
        SELECT 1 FROM t_Roles r
                          JOIN t_ModelRoles mr ON r.id = mr.role_id
                          JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id
          AND mr.model_type = 'UserID'
          AND rp.permission_id = @PermissionId
    )
    )
  AND NOT EXISTS (
    SELECT 1 FROM t_WorkFlowPending p
    WHERE p.Source = @Source
      AND p.SourceID = @SourceId
      AND p.Stage = CAST(@NextStageId AS NVARCHAR(50))
      AND p.UserId = u.Id
      AND p.DeletedOn IS NULL
)
ORDER BY u.Id;
END
END
ELSE IF @WorkflowType = 'MAJ'
BEGIN
SELECT @RequiredApprovals = CEILING(COUNT(*) * 1.0 / 2)
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    EXISTS (
        SELECT 1 FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId
          AND mp.model_type = 'UserID'
          AND mp.model_id = u.Id
    )
        OR
    EXISTS (
        SELECT 1 FROM t_Roles r
                          JOIN t_ModelRoles mr ON r.id = mr.role_id
                          JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id
          AND mr.model_type = 'UserID'
          AND rp.permission_id = @PermissionId
    )
    );

INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
SELECT TOP (@RequiredApprovals) @Source, @SourceId, CAST(@NextStageId AS NVARCHAR(50)), u.Id,
       @SystemUserId, @Now, @SystemUserId, @Now
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    EXISTS (
        SELECT 1 FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId
          AND mp.model_type = 'UserID'
          AND mp.model_id = u.Id
    )
        OR
    EXISTS (
        SELECT 1 FROM t_Roles r
                          JOIN t_ModelRoles mr ON r.id = mr.role_id
                          JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id
          AND mr.model_type = 'UserID'
          AND rp.permission_id = @PermissionId
    )
    )
  AND NOT EXISTS (
    SELECT 1 FROM t_WorkFlowPending p
    WHERE p.Source = @Source
      AND p.SourceID = @SourceId
      AND p.Stage = CAST(@NextStageId AS NVARCHAR(50))
      AND p.UserId = u.Id
      AND p.DeletedOn IS NULL
);
END

                    -- Special handling for AMT workflow type
                    IF @WorkflowType = 'AMT'
BEGIN
INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
SELECT @Source, @SourceId, CAST(@NextStageId AS NVARCHAR(50)), u.Id,
       @SystemUserId, @Now, @SystemUserId, @Now
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND u.Id IN (
    SELECT w.userId
    FROM t_WorkFlowStages s
             LEFT JOIN t_WorkFlowLimits w ON w.MaxAmount >= s.Amount
    WHERE s.[Order] = @NextStageId
    )
  AND NOT EXISTS (
SELECT 1 FROM t_WorkFlowPending p
WHERE p.Source = @Source
  AND p.SourceID = @SourceId
  AND p.Stage = CAST(@NextStageId AS NVARCHAR(50))
  AND p.UserId = u.Id
  AND p.DeletedOn IS NULL
    );
END
END
END

FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId;
END

CLOSE approval_cursor;
DEALLOCATE approval_cursor;

DROP TABLE #ApprovalItems;

END TRY

BEGIN CATCH
        DECLARE @ErrMsg NVARCHAR(4000), @ErrSev INT, @ErrState INT;
SELECT @ErrMsg = ERROR_MESSAGE(), @ErrSev = ERROR_SEVERITY(), @ErrState = ERROR_STATE();
RAISERROR(@ErrMsg, @ErrSev, @ErrState);
END CATCH
END;

-- Example execution:
-- exec p_ProcessWorkflowStages_redo @SystemUserId = 1, @WorkflowStagePermission = 29;
