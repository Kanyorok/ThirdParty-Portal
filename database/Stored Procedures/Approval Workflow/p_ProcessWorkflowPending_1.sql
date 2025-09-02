CREATE or alter PROCEDURE dbo.p_ProcessWorkflowPending_1
    @SystemUserId BIGINT,
    @WorkflowStagePermission BIGINT
    AS
BEGIN
    SET NOCOUNT ON;

    DECLARE
@permissionName NVARCHAR(255),
        @UserHasPermissions BIT = 0,
        @SubmittedStatusId BIGINT,
        @ApprovedStatusId BIGINT,
        @RejectedStatusId BIGINT,
        @Now DATETIME = GETDATE();

    -- Get the permission name for the given workflow stage permission
SELECT @permissionName = [name]
FROM t_Permissions
WHERE id = @WorkflowStagePermission;

-- Check if the user has the required permission
SELECT @UserHasPermissions = CASE WHEN EXISTS (
    SELECT 1
    FROM [t_Users] u
        WHERE u.Id = @SystemUserId
          AND u.DeletedOn IS NULL
          AND (
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
END

    -- Get workflow status IDs for submitted, approved, and rejected
SELECT @SubmittedStatusId = id FROM t_codeDetails WHERE [Description] = 'Submitted for Approval';
SELECT @ApprovedStatusId = id FROM t_codeDetails WHERE [Description] = 'Approved';
SELECT @RejectedStatusId = id FROM t_codeDetails WHERE [Description] = 'Rejected';

IF @SubmittedStatusId IS NULL OR @ApprovedStatusId IS NULL OR @RejectedStatusId IS NULL
BEGIN
        RAISERROR('Required workflow status IDs missing.', 16, 1);
        RETURN;
END

    DECLARE
@Source NVARCHAR(255),
        @SourceID NVARCHAR(100),
        @StageId BIGINT,
        @PermissionId BIGINT,
        @WorkflowType NVARCHAR(50),
        @Count INT;

    -- Cursor to iterate over pending workflow submissions
    DECLARE history_cursor CURSOR FOR
SELECT
    h.Source,
    h.SourceID,
    s.Id,
    s.PermissionId,
    wt.TypeID,
    ISNULL(s.[Count], 0)
FROM t_WorkFlowHistory h
         INNER JOIN t_WorkFlows wf ON h.Source = wf.Source AND wf.DeletedOn IS NULL
         INNER JOIN t_WorkFlowStages s ON wf.Id = s.WorkFlowId AND s.[Order] = 1 AND s.DeletedOn IS NULL
    INNER JOIN t_WorkFlowTypes wt ON s.WorkFlowTypeId = wt.Id
WHERE h.StatusId = @SubmittedStatusId AND h.DeletedOn IS NULL;

OPEN history_cursor;
FETCH NEXT FROM history_cursor INTO @Source, @SourceID, @StageId, @PermissionId, @WorkflowType, @Count;

WHILE @@FETCH_STATUS = 0
BEGIN
        DECLARE @InsertCount INT;
        DECLARE @InsertedPending TABLE (
            Source NVARCHAR(255),
            SourceID NVARCHAR(100),
            Stage NVARCHAR(50),
            UserId BIGINT
        );

        IF @WorkflowType = 'ALL'
BEGIN
INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
    OUTPUT INSERTED.Source, INSERTED.SourceID, INSERTED.Stage, INSERTED.UserId
            INTO @InsertedPending
SELECT
    @Source, @SourceID, CAST(@StageId AS NVARCHAR(50)), u.Id,
    @SystemUserId, @Now, @SystemUserId, @Now
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    EXISTS (
        SELECT 1 FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId AND mp.model_type = 'UserID' AND mp.model_id = u.Id
    )
        OR
    EXISTS (
        SELECT 1 FROM t_Roles r
                          INNER JOIN t_ModelRoles mr ON r.id = mr.role_id
                          INNER JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id AND mr.model_type = 'UserID' AND rp.permission_id = @PermissionId
    )
    )
  AND NOT EXISTS (
    SELECT 1 FROM t_WorkFlowPending p
    WHERE p.Source = @Source AND p.SourceID = @SourceID
      AND p.Stage = CAST(@StageId AS NVARCHAR(50)) AND p.UserId = u.Id AND p.DeletedOn IS NULL
)
  AND NOT EXISTS (
    SELECT 1 FROM t_WorkFlowHistory h2
    WHERE h2.Source = @Source AND h2.SourceID = @SourceID
      AND h2.CreatedBy = u.Id AND h2.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
      AND h2.DeletedOn IS NULL
);
END
ELSE IF @WorkflowType = 'CNT'
BEGIN
INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
    OUTPUT INSERTED.Source, INSERTED.SourceID, INSERTED.Stage, INSERTED.UserId
            INTO @InsertedPending
SELECT TOP (@Count)
            @Source, @SourceID, CAST(@StageId AS NVARCHAR(50)), u.Id,
       @SystemUserId, @Now, @SystemUserId, @Now
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    EXISTS (
        SELECT 1 FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId AND mp.model_type = 'UserID' AND mp.model_id = u.Id
    )
        OR
    EXISTS (
        SELECT 1 FROM t_Roles r
                          INNER JOIN t_ModelRoles mr ON r.id = mr.role_id
                          INNER JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id AND mr.model_type = 'UserID' AND rp.permission_id = @PermissionId
    )
    )
  AND NOT EXISTS (
    SELECT 1 FROM t_WorkFlowPending p
    WHERE p.Source = @Source AND p.SourceID = @SourceID
      AND p.Stage = CAST(@StageId AS NVARCHAR(50)) AND p.UserId = u.Id AND p.DeletedOn IS NULL
)
  AND NOT EXISTS (
    SELECT 1 FROM t_WorkFlowHistory h2
    WHERE h2.Source = @Source AND h2.SourceID = @SourceID
      AND h2.CreatedBy = u.Id AND h2.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
      AND h2.DeletedOn IS NULL
);
END
ELSE IF @WorkflowType = 'MAJ'
BEGIN
            -- Calculate half the count of users eligible for permission
SELECT @InsertCount = CEILING(COUNT(*) * 1.0 / 2)
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    EXISTS (
        SELECT 1 FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId AND mp.model_type = 'UserID' AND mp.model_id = u.Id
    )
        OR
    EXISTS (
        SELECT 1 FROM t_Roles r
                          INNER JOIN t_ModelRoles mr ON r.id = mr.role_id
                          INNER JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id AND mr.model_type = 'UserID' AND rp.permission_id = @PermissionId
    )
    );

INSERT INTO t_WorkFlowPending (
    Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
)
    OUTPUT INSERTED.Source, INSERTED.SourceID, INSERTED.Stage, INSERTED.UserId
            INTO @InsertedPending
SELECT TOP (@InsertCount)
            @Source, @SourceID, CAST(@StageId AS NVARCHAR(50)), u.Id,
       @SystemUserId, @Now, @SystemUserId, @Now
FROM t_Users u
WHERE u.DeletedOn IS NULL
  AND (
    EXISTS (
        SELECT 1 FROM t_ModelPermissions mp
        WHERE mp.permission_id = @PermissionId AND mp.model_type = 'UserID' AND mp.model_id = u.Id
    )
        OR
    EXISTS (
        SELECT 1 FROM t_Roles r
                          INNER JOIN t_ModelRoles mr ON r.id = mr.role_id
                          INNER JOIN t_RolePermissions rp ON r.id = rp.role_id
        WHERE mr.model_id = u.Id AND mr.model_type = 'UserID' AND rp.permission_id = @PermissionId
    )
    )
  AND NOT EXISTS (
    SELECT 1 FROM t_WorkFlowPending p
    WHERE p.Source = @Source AND p.SourceID = @SourceID
      AND p.Stage = CAST(@StageId AS NVARCHAR(50)) AND p.UserId = u.Id AND p.DeletedOn IS NULL
)
  AND NOT EXISTS (
    SELECT 1 FROM t_WorkFlowHistory h2
    WHERE h2.Source = @Source AND h2.SourceID = @SourceID
      AND h2.CreatedBy = u.Id AND h2.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
      AND h2.DeletedOn IS NULL
);
END

        -- Send notification emails for each inserted pending workflow
        DECLARE
@PendingSource NVARCHAR(255),
            @PendingSourceID NVARCHAR(100),
            @PendingStage NVARCHAR(50),
            @PendingUserId BIGINT,
            @UserEmail NVARCHAR(255),
            @EmailMessage NVARCHAR(MAX);

        DECLARE email_cursor CURSOR FOR
SELECT Source, SourceID, Stage, UserId FROM @InsertedPending;

OPEN email_cursor;
FETCH NEXT FROM email_cursor INTO @PendingSource, @PendingSourceID, @PendingStage, @PendingUserId;

WHILE @@FETCH_STATUS = 0
BEGIN
SELECT @UserEmail = Email FROM t_Users WHERE Id = @PendingUserId;

IF @UserEmail IS NOT NULL
BEGIN
                SET @EmailMessage = 'You have been assigned to a new workflow task for Source: ' + @PendingSource + ', ID: ' + @PendingSourceID;

INSERT INTO t_Emails (
    MailID, [Type], [Status], [Priority], [From], [To],
    [Subject], [Body], [Text], [Dated],
    CreatedBy, CreatedOn, ModifiedBy, ModifiedOn,
    [Source], [SourceID]
)
VALUES (
           NEWID(), 'N', 'P', 'M',
           'no-reply@yourdomain.com', @UserEmail,
           'You have a new workflow item pending approval',
           @EmailMessage, @EmailMessage, @Now,
           @SystemUserId, @Now, @SystemUserId, @Now,
           @PendingSource, @PendingSourceID
       );
END

FETCH NEXT FROM email_cursor INTO @PendingSource, @PendingSourceID, @PendingStage, @PendingUserId;
END

CLOSE email_cursor;
DEALLOCATE email_cursor;

        -- Soft delete processed workflow history to avoid reprocessing
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
END

CLOSE history_cursor;
DEALLOCATE history_cursor;
END;

-- Example execution:
--EXEC p_ProcessWorkflowPending_1 @SystemUserId = 2, @WorkflowStagePermission = 29;
