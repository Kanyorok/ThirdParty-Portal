CREATE or ALTER PROCEDURE [dbo].[p_ProcessWorkflowAction_redo1]
    -- @ActionType NVARCHAR(20), -- 'approve' or 'reject'
    @Source NVARCHAR(255),
    @SourceID NVARCHAR(100),
    @UserID BIGINT,
    @UserName NVARCHAR(255) = NULL,
    @Notes NVARCHAR(MAX) = NULL,
    @StatusColumn NVARCHAR(100) = 'Status',
    @StatusID BIGINT
    -- @ApprovedStatusID BIGINT = 14,  -- From t_CodeDetails
    -- @RejectedStatusID BIGINT = 12   -- From t_CodeDetails
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE @StageID NVARCHAR(200) = NULL,
        @WorkFlowID BIGINT = NULL,
        @CurrentStatus NVARCHAR(50) = NULL,
        @HasPendingApprovals BIT = 0,
        @WorkflowStagePermission BIGINT=NULL,-- addded to check perm
        @Description NVARCHAR(50),
        @UserHasPermissions smallint= 0;

    BEGIN TRY
        BEGIN TRANSACTION;
        -- Get current workflow info and set status ID based on action
        SELECT @StageID = p.Stage,
               @WorkFlowID = ws.WorkFlowID,
               @WorkflowStagePermission = ws.PermissionId-- addded to check perm

        -- @StatusID = CASE @ActionType WHEN 'approve' THEN @ApprovedStatusID ELSE @RejectedStatusID END
        --@StatusID = @StatusID
        FROM dbo.t_WorkFlowPending p
                 JOIN dbo.t_WorkFlowStages ws ON p.Stage = ws.Id
        WHERE p.Source = @Source
          AND p.SourceID = @SourceID
          AND p.UserId = @UserID
          AND p.DeletedOn IS NULL;

        -- check if user has permissions
        declare @permissionName nvarchar(100)
        select @permissionName = name from t_Permissions where id = @WorkflowStagePermission
        select @UserHasPermissions = CASE
                                         WHEN EXISTS (select 1
                                                      from [t_Users]
                                                      where (exists
                                                                 (select *
                                                                  from [t_Roles]
                                                                           inner join [t_ModelRoles] on
                                                                      [t_Roles].[id] = [t_ModelRoles].[role_id]
                                                                  where [t_Users].[Id] = [t_ModelRoles].[model_id]
                                                                    and [t_ModelRoles].[model_type] = 'UserID'
                                                                    and exists (select *
                                                                                from [t_Permissions]
                                                                                         inner join [t_RolePermissions]
                                                                                                    on [t_Permissions].[id] = [t_RolePermissions].[permission_id]
                                                                                where [t_Roles].[id] = [t_RolePermissions].[role_id]
                                                                                  and [name] in (@permissionName)))
                                                          or
                                                             exists
                                                                 (select *
                                                                  from [t_Permissions]
                                                                           inner join [t_ModelPermissions] on
                                                                      [t_Permissions].[id] = [t_ModelPermissions].[permission_id]
                                                                  where [t_Users].[Id] = [t_ModelPermissions].[model_id]
                                                                    and [t_ModelPermissions].[model_type] = 'UserID'
                                                                    and [name] in (@permissionName))
                                                          )
                                                        and [t_Users].[DeletedOn] is null
                                                        and [t_Users].Id = @UserID) THEN 1
                                         ELSE 0 END;

        -- Get status value from t_CodeDetails
        DECLARE @StatusValue NVARCHAR(50)

        SELECT @StatusValue = Value,
               @Description = Description
        FROM t_CodeDetails
        WHERE ID = @StatusID;

        -- Check if there are any pending approvals for this item
        SELECT @HasPendingApprovals = CASE
                                          WHEN EXISTS (SELECT 1
                                                       FROM dbo.t_WorkFlowPending
                                                       WHERE Source = @Source
                                                         AND SourceID = @SourceID
                                                         AND DeletedOn IS NULL) THEN 1
                                          ELSE 0 END;

        -- Set current status
        SET @CurrentStatus = CASE WHEN @HasPendingApprovals = 1 THEN 'Pending' ELSE 'Completed' END;

        -- Validate action

        IF @StageID IS NULL
            BEGIN
                ROLLBACK TRANSACTION;
                SELECT 'ERROR' AS Status, 'No pending approval found for this user' AS Message;
                RETURN;
            END

        --check if user has perm
        if @UserHasPermissions = 0
            begin
                ROLLBACK TRANSACTION;
                SELECT 'ERROR' AS Status, 'User has no permissions' AS Message;
                RETURN;
            end


        -- Record action in history
        INSERT INTO dbo.t_WorkFlowHistory (Source, SourceID, Stage, Notes, StatusId,
                                           CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
        VALUES (@Source, @SourceID, @StageID, @Notes, @StatusID,
                @UserID, GETDATE(), @UserID, GETDATE());

        -- Mark approval as processed
        -- chec
        UPDATE dbo.t_WorkFlowPending
        SET DeletedBy = @UserID,
            DeletedOn = GETDATE(),
            ModifiedBy = @UserID,
            ModifiedOn = GETDATE()
        WHERE Source = @Source
          AND SourceID = @SourceID
          AND UserId = @UserID;

        -- Update source table if rejected or final approval
        --IF @ActionType = 'reject' OR @HasPendingApprovals = 0
        IF @HasPendingApprovals = 1
            BEGIN
                DECLARE @LastApproverColumn NVARCHAR(100) = '';
                DECLARE @UpdateSQL NVARCHAR(MAX);
                DECLARE @KeyColumn NVARCHAR(50) = 'Id';

                -- Check if LastApprover column exists
                IF EXISTS (SELECT 1
                           FROM INFORMATION_SCHEMA.COLUMNS
                           WHERE TABLE_NAME = PARSENAME(@Source, 1)
                             AND COLUMN_NAME = 'LastApprover')
                    BEGIN
                        SET @LastApproverColumn = ', LastApprover = @UserName';
                    END

                -- Find key column name (Id or ID)
                SELECT TOP 1 @KeyColumn = COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = PARSENAME(@Source, 1)
                  AND COLUMN_NAME IN ('Id', 'ID');

                IF @KeyColumn IS NULL
                    SET @KeyColumn = 'Id';

                -- Build dynamic update SQL
                SET @UpdateSQL = N'
                UPDATE ' + QUOTENAME(@Source)
                    + 'SET '
                    + QUOTENAME(@StatusColumn) + ' =  @StatusID,
                   ModifiedBy = @UserID,
                    ModifiedOn = GETDATE()'
                    + @LastApproverColumn + '
                WHERE ' + QUOTENAME(@KeyColumn) + ' = @SourceID';

                -- Execute dynamic SQL
                EXEC sp_executesql @UpdateSQL,
                     N'@StatusID NVARCHAR(50), @UserName NVARCHAR(255), @SourceID NVARCHAR(100), @UserID BIGINT',
                     @StatusID, @UserName, @SourceID, @UserID;

                -- Update FinalStage if fully approved
                -- IF @ActionType = 'approve' AND @HasPendingApprovals = 0
                --IF @HasPendingApprovals = 0
                --BEGIN
                --    DECLARE @FinalStageID BIGINT;
                --    SELECT TOP 1 @FinalStageID = ID
                --    FROM t_CodeDetails
                --    WHERE Value = 'FINAL' AND DeletedOn IS NULL;

                --    IF @FinalStageID IS NOT NULL
                --    BEGIN
                --        UPDATE t_Workflows
                --        SET FinalStage = @FinalStageID
                --        WHERE Id = @StageID;
                --    END
                --END
            END
        COMMIT TRANSACTION;

        SELECT 'SUCCESS'                  AS Status,
               --CASE WHEN @ActionType = 'approve' THEN 'Approval recorded' ELSE 'Rejection recorded' END AS Message,
               @Description + ' Recorded' as Message,
               @Description               AS WorkflowStatus;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        SELECT 'ERROR' AS Status,
               ERROR_MESSAGE() AS Message,
               NULL    AS WorkflowStatus;
    END CATCH
END


--exec p_ProcessWorkflowAction_redo1
---- @ActionType = 'approve',
---- 'approve' or 'reject'
--@Source = 't_Tickets',
--@SourceID = '1',
--@UserID = 2,
--@UserName  = NULL,
--@Notes  = NULL,
--@StatusColumn  = 'Status',
--@StatusID  = '32'
