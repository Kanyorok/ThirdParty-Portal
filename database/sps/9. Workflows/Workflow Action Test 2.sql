USE [BR_ERP]
GO

/****** Object:  StoredProcedure [dbo].[p_ProcessWorkflowActionTest]    Script Date: 1/6/2026 11:47:26 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE   PROCEDURE [dbo].[p_ProcessWorkflowActionTest]
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
        @StatusValue NVARCHAR(50),
        @IsMakerCheckerViolation BIT = 0,
        @ViolationReason NVARCHAR(500) = NULL,
        @SourceIDInt INT;

    BEGIN TRY
       BEGIN TRANSACTION;

        -- 1. VALIDATE SOURCEID CONVERSION
        SET @SourceIDInt = TRY_CAST(@SourceID AS INT);

        IF @SourceIDInt IS NULL
        BEGIN
            ROLLBACK TRANSACTION;
            SELECT 'ERROR' AS Status, 'SourceID must be a valid integer' AS Message;
            RETURN;
        END

        -- 2. GET CURRENT WORKFLOW INFO
        SELECT
            @StageID = p.Stage,
            @WorkFlowID = ws.WorkFlowId,  -- ⭐ lowercase "Id"
            @WorkflowStagePermission = ws.PermissionId
        FROM t_WorkFlowPendingTest p  -- ⭐ Uppercase "Test"
        JOIN t_WorkFlowStagesTest ws ON TRY_CAST(p.Stage AS BIGINT) = ws.Id  -- ⭐ Uppercase "Test" + TRY_CAST
        WHERE p.Source = @Source
          AND p.SourceID = @SourceID
          AND p.UserId = @UserID
          AND p.DeletedOn IS NULL;

        -- Validate action FIRST - if no workflow found
        IF @StageID IS NULL
        BEGIN
            ROLLBACK TRANSACTION;
            SELECT 'ERROR' AS Status, 'No pending approval found for this user' AS Message;
            RETURN;
        END

        -- 3. CHECK MAKER-CHECKER VIOLATION
        SELECT
            @IsMakerCheckerViolation = IsViolation,
            @ViolationReason = FailureReason
        FROM dbo.f_CheckMakerCheckerViolation(@Source, @SourceIDInt, @UserID);

        IF @IsMakerCheckerViolation = 1
        BEGIN
            ROLLBACK TRANSACTION;
            SELECT 'ERROR' AS Status,
                   ISNULL(@ViolationReason, 'Maker-Checker violation detected') AS Message;
            RETURN;
        END

        -- 4. CHECK USER PERMISSIONS
        SET @UserHasPermissions = [dbo].[f_CheckUserPermission](@UserID, @WorkflowStagePermission);
        IF @UserHasPermissions = 0
        BEGIN
            ROLLBACK TRANSACTION;
            SELECT 'ERROR' AS Status, 'User has no permissions' AS Message;
            RETURN;
        END

        -- 5. VALIDATE STATUS ID
        IF NOT EXISTS (SELECT 1 FROM t_CodeDetails WHERE ID = @StatusID)
        BEGIN
            ROLLBACK TRANSACTION;
            SELECT 'ERROR' AS Status,
                   'Status ID not found: ' + CAST(@StatusID AS NVARCHAR(20)) AS Message,
                   NULL AS WorkflowStatus;
            RETURN;
        END

        -- Get status values
        SELECT
            @StatusValue = Value,
            @Description = Description
        FROM t_CodeDetails
        WHERE ID = @StatusID;

        -- 6. CHECK FOR PENDING APPROVALS
        SELECT @HasPendingApprovals = CASE WHEN EXISTS (
            SELECT 1 FROM t_WorkFlowPendingTest  -- ⭐ Uppercase "Test"
            WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL
        ) THEN 1 ELSE 0 END;

        -- Set current status
        SET @CurrentStatus = CASE WHEN @HasPendingApprovals = 1 THEN 'Pending' ELSE 'Completed' END;

        -- 7. RECORD ACTION IN HISTORY
        INSERT INTO t_WorkFlowHistoryTest (
            Source, SourceID, Stage, Notes, StatusId,
            CreatedBy, CreatedOn, ModifiedBy, ModifiedOn, IsApproved
        )
        VALUES (
            @Source, @SourceID, @StageID, @Notes, @StatusID,
            @UserID, GETDATE(), @UserID, GETDATE(), @IsApproved
        );

        -- 8. MARK APPROVAL AS PROCESSED
        UPDATE t_WorkFlowPendingTest  -- ⭐ Uppercase "Test"
        SET DeletedBy = @UserID,
            DeletedOn = GETDATE(),
            ModifiedBy = @UserID,
            ModifiedOn = GETDATE()
        WHERE Source = @Source AND SourceID = @SourceID AND UserId = @UserID;

        -- 9. UPDATE SOURCE TABLE IF REJECTED OR FINAL APPROVAL
        IF @HasPendingApprovals = 1
        BEGIN
            DECLARE @LastApproverColumn NVARCHAR(100) = '';
            DECLARE @UpdateSQL NVARCHAR(MAX);
            DECLARE @KeyColumn NVARCHAR(50) = 'Id';
            DECLARE @TableName NVARCHAR(255) = PARSENAME(@Source, 1);

            -- Validate the table exists
            IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = @TableName)
            BEGIN
                ROLLBACK TRANSACTION;
                SELECT 'ERROR' AS Status, 'Source table does not exist: ' + @Source AS Message;
                RETURN;
            END

            -- Validate StatusColumn exists
            IF NOT EXISTS (
                SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = @TableName AND COLUMN_NAME = @StatusColumn
            )
            BEGIN
                ROLLBACK TRANSACTION;
                SELECT 'ERROR' AS Status,
                       'Status column "' + @StatusColumn + '" not found in table: ' + @Source AS Message;
                RETURN;
            END

            -- Validate ModifiedBy column exists
            IF NOT EXISTS (
                SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'ModifiedBy'
            )
            BEGIN
                ROLLBACK TRANSACTION;
                SELECT 'ERROR' AS Status,
                       'ModifiedBy column not found in table: ' + @Source AS Message;
                RETURN;
            END

            -- Validate ModifiedOn column exists
            IF NOT EXISTS (
                SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'ModifiedOn'
            )
            BEGIN
                ROLLBACK TRANSACTION;
                SELECT 'ERROR' AS Status,
                       'ModifiedOn column not found in table: ' + @Source AS Message;
                RETURN;
            END

            -- Check if LastApprover column exists (optional)
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

        -- 10. EMAIL NOTIFICATION
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

        -- 11. RETURN SUCCESS
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

--exec
--[p_ProcessWorkflowActionTest]
--    @Source = 't_FinanceJournalEntries'
--    ,@SourceID = '4'
--    ,@UserID = '35'
--    ,@StatusColumn = 'status'
--    ,@StatusID = '29'
--    ,@IsApproved = 1
 
GO

