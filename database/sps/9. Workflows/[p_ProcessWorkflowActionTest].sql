CREATE OR ALTER PROCEDURE [dbo].[p_ProcessWorkflowActionTest]
    @Source NVARCHAR(255),
    @SourceID NVARCHAR(100),
    @UserID BIGINT,
    @UserName NVARCHAR(255) = NULL,
    @Notes NVARCHAR(MAX) = NULL,
    @StatusColumn NVARCHAR(100) = 'Status',
    @StatusID BIGINT,
    @IsApproved BIT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE
        @StageID NVARCHAR(200) = NULL,
        @WorkFlowID BIGINT = NULL,
        @CurrentStatus NVARCHAR(50) = NULL,
        @HasPendingApprovals BIT = 0,
        @WorkflowStagePermission BIGINT = NULL,
        @Description NVARCHAR(50),
        @UserHasPermissions SMALLINT = 0,
        @UserEmail NVARCHAR(255),
        @EmailMessage NVARCHAR(MAX),
        @EmailSubject NVARCHAR(255),
        @StatusValue NVARCHAR(50);  -- Added declaration

    BEGIN TRY
       BEGIN TRANSACTION;

        -- Get current workflow info and set status ID based on action
        SELECT
            @StageID = p.Stage,
            @WorkFlowID = ws.WorkFlowID,
            @WorkflowStagePermission = ws.PermissionId
        FROM dbo.t_WorkFlowPending p
        JOIN dbo.t_WorkFlowStages ws ON p.Stage = ws.Id
        WHERE p.Source = @Source
          AND p.SourceID = @SourceID
          AND p.UserId = @UserID
          AND p.DeletedOn IS NULL;

        -- ✅ SIMPLIFIED: Check if user has permission using new function
        SET @UserHasPermissions = [dbo].[f_CheckUserPermission](@UserID, @WorkflowStagePermission);

        -- Get status value from t_CodeDetails
        SELECT
            @StatusValue = Value,
            @Description = Description
        FROM t_CodeDetails
        WHERE ID = @StatusID;

        -- Check if there are any pending approvals for this item
        SELECT @HasPendingApprovals = CASE WHEN EXISTS (
            SELECT 1 FROM dbo.t_WorkFlowPending
            WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL
        ) THEN 1 ELSE 0 END;

        -- Set current status
        SET @CurrentStatus = CASE WHEN @HasPendingApprovals = 1 THEN 'Pending' ELSE 'Completed' END;

        -- Validate action
        IF @StageID IS NULL
        BEGIN
            ROLLBACK TRANSACTION;
            SELECT 'ERROR' AS Status, 'No pending approval found for this user' AS Message;
            RETURN;
        END

        -- Check if user has permission
        IF @UserHasPermissions = 0
        BEGIN
            ROLLBACK TRANSACTION;
            SELECT 'ERROR' AS Status, 'User has no permissions' AS Message;
            RETURN;
        END

        -- Record action in history
        INSERT INTO dbo.t_WorkFlowHistory (
            Source, SourceID, Stage, Notes, StatusId,
            CreatedBy, CreatedOn, ModifiedBy, ModifiedOn, IsApproved
        )
        VALUES (
            @Source, @SourceID, @StageID, @Notes, @StatusID,
            @UserID, GETDATE(), @UserID, GETDATE(), @IsApproved
        );

        -- Mark approval as processed
        UPDATE dbo.t_WorkFlowPending
        SET DeletedBy = @UserID,
            DeletedOn = GETDATE(),
            ModifiedBy = @UserID,
            ModifiedOn = GETDATE()
        WHERE Source = @Source AND SourceID = @SourceID AND UserId = @UserID;

        -- Update source table if rejected or final approval
        IF @HasPendingApprovals = 1
        BEGIN
            DECLARE @LastApproverColumn NVARCHAR(100) = '';
            DECLARE @UpdateSQL NVARCHAR(MAX);
            DECLARE @KeyColumn NVARCHAR(50) = 'Id';

            -- Check if LastApprover column exists
            IF EXISTS (
                SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = PARSENAME(@Source, 1)
                  AND COLUMN_NAME = 'LastApprover'
            )
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
            SET @UpdateSQL = N'UPDATE ' + QUOTENAME(@Source) + ' SET ' +
                QUOTENAME(@StatusColumn) + ' = @StatusID, ' +
                'ModifiedBy = @UserID, ModifiedOn = GETDATE()' +
                @LastApproverColumn + ' WHERE ' + QUOTENAME(@KeyColumn) + ' = @SourceID';

            -- Execute dynamic SQL
            EXEC sp_executesql @UpdateSQL,
                N'@StatusID BIGINT, @UserName NVARCHAR(255), @SourceID NVARCHAR(100), @UserID BIGINT',
                @StatusID, @UserName, @SourceID, @UserID;
        END

        COMMIT TRANSACTION;

        -- ✅ EMAIL NOTIFICATION BLOCK
        SELECT @UserEmail = Email FROM t_Users WHERE Id = @UserID;

        IF @UserEmail IS NOT NULL AND LEN(@UserEmail) > 5
        BEGIN
            SET @EmailMessage =
                'Your workflow action for Source: ' + ISNULL(@Source, '[Unknown]') +
                ', ID: ' + ISNULL(@SourceID, '[Unknown]') +
                ' has been recorded as: ' + ISNULL(@Description, '[Unknown]') + '.';

            SET @EmailSubject =
                'Workflow Action Confirmation - ' + ISNULL(@Description, '[Unknown]');

            EXEC p_sendNotificationEmail
                @UserID = @UserID,
                @Subject = @EmailSubject,
                @Message = @EmailMessage,
                @SenderId = @UserID,
                @Source = @Source,
                @SourceID = @SourceID;
        END
        -- ✅ EMAIL NOTIFICATION BLOCK ENDS

        SELECT 'SUCCESS' AS Status,
               @Description + ' Recorded' AS Message,
               @Description AS WorkflowStatus;

    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;
        SELECT 'ERROR' AS Status,
               ERROR_MESSAGE() AS Message,
               NULL AS WorkflowStatus;
    END CATCH
END
GO
