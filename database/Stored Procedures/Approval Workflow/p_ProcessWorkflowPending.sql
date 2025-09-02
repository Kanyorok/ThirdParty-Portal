CREATE Or alter PROCEDURE [dbo].[p_ProcessWorkflowPending]

AS
BEGIN
    SET NOCOUNT ON;

    DECLARE
@permissionName NVARCHAR(255),
        @UserHasPermissions BIT = 0,
        @SubmittedStatusId BIGINT,
        @ApprovedStatusId BIGINT,
        @RejectedStatusId BIGINT,
        @Now DATETIME = GETDATE(),
        @SystemUserId BIGINT,
        @WorkflowStagePermission BIGINT;

    --//check from t_workflowstages

    select @SystemUserId = s.CreatedBy, @WorkflowStagePermission = s.PermissionId from t_workflowstages s;

    -- Get the permission name for the given WorkflowStagePermission
SELECT @permissionName = [name]
FROM t_Permissions
WHERE id = @WorkflowStagePermission;

-- Check if the user has the required permission (direct or via role)
SELECT @UserHasPermissions = CASE WHEN EXISTS (
    SELECT 1
    FROM [t_Users] u
        WHERE
            u.Id = @SystemUserId
            AND u.DeletedOn IS NULL
            AND (
                -- Check role-based permissions
                EXISTS (
                    SELECT 1
                    FROM [t_Roles] r
                    INNER JOIN [t_ModelRoles] mr ON r.id = mr.role_id
                    INNER JOIN [t_RolePermissions] rp ON r.id = rp.role_id
                    INNER JOIN [t_Permissions] p ON rp.permission_id = p.id
                    WHERE mr.model_id = u.Id
                      AND mr.model_type = 'UserID'
                      AND p.name = @permissionName
                )
                OR
                -- Check direct permissions
                EXISTS (
                    SELECT 1
                    FROM [t_ModelPermissions] mp
                    INNER JOIN [t_Permissions] p ON mp.permission_id = p.id
                    WHERE mp.model_id = u.Id
                      AND mp.model_type = 'UserID'
                      AND p.name = @permissionName
                )
            )
) THEN 1 ELSE 0 END;

IF @UserHasPermissions = 0
BEGIN
        RAISERROR('User does not have the required permission.', 16, 1);
        RETURN;
END;

    -- Get status IDs from t_codeDetails
SELECT @SubmittedStatusId = id FROM t_codeDetails WHERE [Description] = 'Submitted for Approval';
SELECT @ApprovedStatusId = id FROM t_codeDetails WHERE [Description] = 'Approved';
SELECT @RejectedStatusId = id FROM t_codeDetails WHERE [Description] = 'Rejected';

IF @SubmittedStatusId IS NULL OR @ApprovedStatusId IS NULL OR @RejectedStatusId IS NULL
BEGIN
        RAISERROR('Required workflow status IDs missing.', 16, 1);
        RETURN;
END;

    -- Declare variables for cursor
    DECLARE @Source NVARCHAR(255), @SourceID NVARCHAR(100), @StageId BIGINT,
            @PermissionId BIGINT, @WorkflowType NVARCHAR(50), @Count INT;

    DECLARE history_cursor CURSOR FOR
SELECT
    h.Source, h.SourceID, s.Id, s.PermissionId, wt.TypeID, ISNULL(s.[Count], 0)
FROM t_WorkFlowHistory h
         INNER JOIN t_WorkFlows wf ON h.Source = wf.Source AND wf.DeletedOn IS NULL
         INNER JOIN t_WorkFlowStages s ON wf.Id = s.WorkFlowId AND s.[Order] = 1 AND s.DeletedOn IS NULL
    INNER JOIN t_WorkFlowTypes wt ON s.WorkFlowTypeId = wt.Id
WHERE h.StatusId = @SubmittedStatusId
  AND h.DeletedOn IS NULL;

OPEN history_cursor;
FETCH NEXT FROM history_cursor INTO @Source, @SourceID, @StageId, @PermissionId, @WorkflowType, @Count;

WHILE @@FETCH_STATUS = 0
BEGIN
        DECLARE @InsertCount INT;

        IF @WorkflowType = 'ALL'
BEGIN
INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
SELECT
    @Source,
    @SourceID,
    CAST(@StageId AS NVARCHAR(50)),
    u.Id,
    @SystemUserId,
    @Now,
    @SystemUserId,
    @Now
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    -- Check direct permissions
    EXISTS (
        SELECT 1
        FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId
          AND mp.model_type = 'UserID'
          AND mp.model_id = u.Id
    )
        OR
        -- Check role-based permissions
    EXISTS (
        SELECT 1
        FROM t_Roles r
                 INNER JOIN t_ModelRoles mr ON r.id = mr.role_id
                 INNER JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id
          AND mr.model_type = 'UserID'
          AND rp.permission_id = @PermissionId
    )
    )
  AND NOT EXISTS (
    SELECT 1
    FROM t_WorkFlowPending p
    WHERE p.Source = @Source
      AND p.SourceID = @SourceID
      AND p.Stage = CAST(@StageId AS NVARCHAR(50))
      AND p.UserId = u.Id
      AND p.DeletedOn IS NULL
)
  AND NOT EXISTS (
    SELECT 1
    FROM t_WorkFlowHistory h2
    WHERE h2.Source = @Source
      AND h2.SourceID = @SourceID
      AND h2.CreatedBy = u.Id
      AND h2.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
      AND h2.DeletedOn IS NULL
);
END
ELSE IF @WorkflowType = 'CNT'
BEGIN
INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
SELECT TOP (@Count)
            @Source,
       @SourceID,
       CAST(@StageId AS NVARCHAR(50)),
       u.Id,
       @SystemUserId,
       @Now,
       @SystemUserId,
       @Now
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    -- Check direct permissions
    EXISTS (
        SELECT 1
        FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId
          AND mp.model_type = 'UserID'
          AND mp.model_id = u.Id
    )
        OR
        -- Check role-based permissions
    EXISTS (
        SELECT 1
        FROM t_Roles r
                 INNER JOIN t_ModelRoles mr ON r.id = mr.role_id
                 INNER JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id
          AND mr.model_type = 'UserID'
          AND rp.permission_id = @PermissionId
    )
    )
  AND NOT EXISTS (
    SELECT 1
    FROM t_WorkFlowPending p
    WHERE p.Source = @Source
      AND p.SourceID = @SourceID
      AND p.Stage = CAST(@StageId AS NVARCHAR(50))
      AND p.UserId = u.Id
      AND p.DeletedOn IS NULL
)
  AND NOT EXISTS (
    SELECT 1
    FROM t_WorkFlowHistory h2
    WHERE h2.Source = @Source
      AND h2.SourceID = @SourceID
      AND h2.CreatedBy = u.Id
      AND h2.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
      AND h2.DeletedOn IS NULL
);
END
ELSE IF @WorkflowType = 'MAJ'
BEGIN
SELECT @InsertCount = CEILING(COUNT(*) * 1.0 / 2)
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    -- Check direct permissions
    EXISTS (
        SELECT 1
        FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId
          AND mp.model_type = 'UserID'
          AND mp.model_id = u.Id
    )
        OR
        -- Check role-based permissions
    EXISTS (
        SELECT 1
        FROM t_Roles r
                 INNER JOIN t_ModelRoles mr ON r.id = mr.role_id
                 INNER JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id
          AND mr.model_type = 'UserID'
          AND rp.permission_id = @PermissionId
    )
    );

INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
SELECT TOP (@InsertCount)
            @Source,
       @SourceID,
       CAST(@StageId AS NVARCHAR(50)),
       u.Id,
       @SystemUserId,
       @Now,
       @SystemUserId,
       @Now
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    -- Check direct permissions
    EXISTS (
        SELECT 1
        FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId
          AND mp.model_type = 'UserID'
          AND mp.model_id = u.Id
    )
        OR
        -- Check role-based permissions
    EXISTS (
        SELECT 1
        FROM t_Roles r
                 INNER JOIN t_ModelRoles mr ON r.id = mr.role_id
                 INNER JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id
          AND mr.model_type = 'UserID'
          AND rp.permission_id = @PermissionId
    )
    )
  AND NOT EXISTS (
    SELECT 1
    FROM t_WorkFlowPending p
    WHERE p.Source = @Source
      AND p.SourceID = @SourceID
      AND p.Stage = CAST(@StageId AS NVARCHAR(50))
      AND p.UserId = u.Id
      AND p.DeletedOn IS NULL
)
  AND NOT EXISTS (
    SELECT 1
    FROM t_WorkFlowHistory h2
    WHERE h2.Source = @Source
      AND h2.SourceID = @SourceID
      AND h2.CreatedBy = u.Id
      AND h2.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
      AND h2.DeletedOn IS NULL
);
END;

        -- Soft delete the history row after processing so it's not processed again
UPDATE t_WorkFlowHistory
SET DeletedOn = @Now,
    DeletedBy = @SystemUserId,
    ModifiedOn = @Now,
    ModifiedBy = @SystemUserId
WHERE Source = @Source
  AND SourceID = @SourceID
  AND Stage = CAST(@StageId AS NVARCHAR(200))
  AND StatusId = @SubmittedStatusId
  AND DeletedOn IS NULL;

FETCH NEXT FROM history_cursor INTO @Source, @SourceID, @StageId, @PermissionId, @WorkflowType, @Count;
END;

CLOSE history_cursor;
DEALLOCATE history_cursor;
END;
GO
