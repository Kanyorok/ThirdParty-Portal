USE [BR_ERP]
GO
/****** Object:  StoredProcedure [dbo].[p_ProcessWorkflowActionTest1]    Script Date: 13/01/2026 14:03:41 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER   PROCEDURE [dbo].[p_ProcessWorkflowActionTest1]
    @Source NVARCHAR(255),
    @SourceID NVARCHAR(100),
    @UserID BIGINT,
    @UserName NVARCHAR(255) = NULL,
    @Notes NVARCHAR(MAX) = NULL,
    @StatusColumn NVARCHAR(100) = 'Status',
    @StatusID BIGINT,
    @IsApproved BIT = NULL,
    @DocumentId BIGINT = NULL,
    @SignatureID BIGINT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON; -- This will automatically rollback and abort on errors

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
        @SourceIDInt INT,
        @ErrorMessage NVARCHAR(4000),
        @ErrorSeverity INT,
        @ErrorState INT,
        @DynamicErrorMessage NVARCHAR(1000),
        @IsDocRequired bit,
        @ValidationErrorMessage NVARCHAR(500) = NULL;

    BEGIN TRY
        BEGIN TRANSACTION;

        -- 1. VALIDATE SOURCEID CONVERSION
        SET @SourceIDInt = TRY_CAST(@SourceID AS INT);

        IF @SourceIDInt IS NULL
        BEGIN
            RAISERROR('SourceID must be a valid integer', 16, 1);
            RETURN;
        END

        -- =====================================================================
        -- DOCUMENT & SIGNATURE VALIDATION (MODULAR)
        -- =====================================================================
        -- Only validate if document or signature parameters are provided
        IF @DocumentId IS NOT NULL OR @SignatureID IS NOT NULL
        BEGIN
            EXEC dbo.p_ValidateWorkflowDocumentSignature
                @Source = @Source,
                @SourceID = @SourceID,
                @UserID = @UserID,
                @DocumentId = @DocumentId,
                @SignatureID = @SignatureID,
                @IsDocRequired = @IsDocRequired OUTPUT,
                @ErrorMessage = @ValidationErrorMessage OUTPUT;

            IF @ValidationErrorMessage IS NOT NULL
            BEGIN
                RAISERROR(@ValidationErrorMessage, 16, 1);
                RETURN;
            END
        END
        -- =====================================================================
        -- END DOCUMENT & SIGNATURE VALIDATION
        -- =====================================================================

        -- 2. CHECK MAKER-CHECKER VIOLATION EARLY (FAIL FAST)
        SELECT
            @IsMakerCheckerViolation = IsViolation,
            @ViolationReason = FailureReason
        FROM dbo.f_CheckMakerCheckerViolation(@Source, @SourceIDInt, @UserID);

        IF @IsMakerCheckerViolation = 1
        BEGIN
            SET @ErrorMessage = ISNULL(@ViolationReason, 'Maker-Checker violation detected');
            RAISERROR(@ErrorMessage, 16, 1);
            RETURN;
        END

        -- 3. GET CURRENT WORKFLOW INFO
        SELECT
            @StageID = p.Stage,
            @WorkFlowID = ws.WorkFlowId,
            @WorkflowStagePermission = ws.PermissionId
        FROM t_WorkFlowPendingTest p
        JOIN t_WorkFlowStagesTest ws ON TRY_CAST(p.Stage AS BIGINT) = ws.Id
        WHERE p.Source = @Source
          AND p.SourceID = @SourceID
          AND p.UserId = @UserID
          AND p.DeletedOn IS NULL;

        -- Validate action - if no workflow found
        IF @StageID IS NULL
        BEGIN
            RAISERROR('No pending approval found for this user', 16, 1);
            RETURN;
        END

        -- 4. CHECK USER PERMISSIONS
        SET @UserHasPermissions = [dbo].[f_CheckUserPermission](@UserID, @WorkflowStagePermission);
        IF @UserHasPermissions = 0
        BEGIN
            RAISERROR('User has no permissions', 16, 1);
            RETURN;
        END

        -- 5. VALIDATE STATUS ID
        IF NOT EXISTS (SELECT 1 FROM t_CodeDetails WHERE ID = @StatusID)
        BEGIN
            SET @ErrorMessage = 'Status ID not found: ' + CAST(@StatusID AS NVARCHAR(20));
            RAISERROR(@ErrorMessage, 16, 1);
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
            SELECT 1 FROM t_WorkFlowPendingTest
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
        UPDATE t_WorkFlowPendingTest
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
                SET @ErrorMessage = 'Source table does not exist: ' + @Source;
                RAISERROR(@ErrorMessage, 16, 1);
                RETURN;
            END

            -- Validate StatusColumn exists
            IF NOT EXISTS (
                SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = @TableName AND COLUMN_NAME = @StatusColumn
            )
            BEGIN
                SET @ErrorMessage = 'Status column "' + @StatusColumn + '" not found in table: ' + @Source;
                RAISERROR(@ErrorMessage, 16, 1);
                RETURN;
            END

            -- Validate ModifiedBy column exists
            IF NOT EXISTS (
                SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'ModifiedBy'
            )
            BEGIN
                SET @ErrorMessage = 'ModifiedBy column not found in table: ' + @Source;
                RAISERROR(@ErrorMessage, 16, 1);
                RETURN;
            END

            -- Validate ModifiedOn column exists
            IF NOT EXISTS (
                SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'ModifiedOn'
            )
            BEGIN
                SET @ErrorMessage = 'ModifiedOn column not found in table: ' + @Source;
                RAISERROR(@ErrorMessage, 16, 1);
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

        -- 10. EMAIL NOTIFICATION (moved before COMMIT to ensure it's part of transaction)
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

        COMMIT TRANSACTION;

        -- 11. RETURN SUCCESS
        SELECT 'SUCCESS' AS Status,
               @Description + ' Recorded' AS Message,
               @Description AS WorkflowStatus;

    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        -- Capture error details
        SELECT
            @ErrorMessage = ERROR_MESSAGE(),
            @ErrorSeverity = ERROR_SEVERITY(),
            @ErrorState = ERROR_STATE();

        -- Re-throw the error to stop continuation
        RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
    END CATCH
END
GO
