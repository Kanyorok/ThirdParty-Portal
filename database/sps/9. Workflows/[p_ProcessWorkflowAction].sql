CREATE OR ALTER PROCEDURE [dbo].[p_ProcessWorkflowAction]
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
    SET XACT_ABORT ON;

    DECLARE
        @StageID NVARCHAR(200) = NULL,
        @StageIDAsInt BIGINT = NULL,
        @WorkFlowID BIGINT = NULL,
        @WorkflowStagePermission BIGINT = NULL,
        @Description NVARCHAR(100),
        @StatusValue NVARCHAR(50),
        @UserHasPermissions SMALLINT = 0,
        @IsMakerCheckerViolation BIT = 0,
        @ViolationReason NVARCHAR(500) = NULL,
        @SourceIDInt INT,
        @ErrorMessage NVARCHAR(4000),
        @ErrorSeverity INT,
        @ErrorState INT,
        -- Document/Signature
        @IsDocRequired BIT = 0,
        @ValidationErrorMessage NVARCHAR(500) = NULL,
        -- Multi-approval
        @WorkflowType NVARCHAR(100),
        @StageName NVARCHAR(255),
        @ConfiguredCount INT = 1,
        @TotalApprovalsRequired INT = 0,
        @CurrentApprovedCount INT = 0,
        @TotalEligibleUsers INT = 0,
        @PendingCountBeforeDelete INT = 0,
        @RemainingPendingCount INT = 0,
        @StageCompleted BIT = 0,
        @HasPendingApprovals BIT = 0,
        @SystemUserId BIGINT,
        @EffectivePermissionId BIGINT,
        @Amount DECIMAL(20, 4) = 0,
        -- Action classification
        @IsApprovalAction BIT = 0,
        @IsRejectionAction BIT = 0,
        -- Email
        @UserEmail NVARCHAR(255),
        @EmailMessage NVARCHAR(MAX),
        @EmailSubject NVARCHAR(255),
        -- Source table update
        @LastApproverColumn NVARCHAR(100) = '',
        @UpdateSQL NVARCHAR(MAX),
        @KeyColumn NVARCHAR(50) = 'Id',
        @TableName NVARCHAR(255);

    BEGIN TRY
        BEGIN TRANSACTION;

        -- =====================================================================
        -- 1. VALIDATE SOURCEID CONVERSION
        -- =====================================================================
        SET @SourceIDInt = TRY_CAST(@SourceID AS INT);
        IF @SourceIDInt IS NULL
        BEGIN
            RAISERROR('SourceID must be a valid integer', 16, 1);
            RETURN;
        END

        -- =====================================================================
        -- 2. VALIDATE STATUS ID & CLASSIFY ACTION
        -- =====================================================================
        SELECT TOP 1
            @StatusValue = Value,
            @Description = Description
        FROM t_CodeDetails WITH (NOLOCK)
        WHERE ID = @StatusID;

        IF @Description IS NULL
        BEGIN
            SET @ErrorMessage = 'Status ID not found: ' + CAST(@StatusID AS NVARCHAR(20));
            RAISERROR(@ErrorMessage, 16, 1);
            RETURN;
        END

        -- Classify action based on description
        IF LOWER(@Description) LIKE '%approve%' OR LOWER(@Description) = 'approval'
            SET @IsApprovalAction = 1;
        ELSE IF LOWER(@Description) LIKE '%reject%'
            SET @IsRejectionAction = 1;

        -- =====================================================================
        -- 3. MAKER-CHECKER VIOLATION CHECK
        -- =====================================================================
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

        -- =====================================================================
        -- 4. DOCUMENT & SIGNATURE VALIDATION
        -- =====================================================================
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
        -- 5. GET CURRENT WORKFLOW STAGE INFO
        -- =====================================================================
        SELECT TOP 1
            @StageID = p.Stage,
            @WorkFlowID = ws.WorkFlowID,
            @WorkflowStagePermission = ws.PermissionId,
            @ConfiguredCount = ISNULL(ws.Count, 1),
            @StageName = ws.StageName,
            @WorkflowType = wt.TypeID
        FROM dbo.t_WorkFlowPending p WITH (NOLOCK)
        JOIN dbo.t_WorkFlowStages ws WITH (NOLOCK) ON TRY_CAST(p.Stage AS BIGINT) = ws.Id
        LEFT JOIN dbo.t_WorkFlowTypes wt WITH (NOLOCK) ON ws.WorkFlowTypeId = wt.Id
        WHERE p.Source = @Source
          AND p.SourceID = @SourceID
          AND p.UserId = @UserID
          AND p.DeletedOn IS NULL;

        IF @StageID IS NULL
        BEGIN
            RAISERROR('No pending approval found for this user or already actioned', 16, 1);
            RETURN;
        END

        SET @StageIDAsInt = TRY_CAST(@StageID AS BIGINT);

        -- =====================================================================
        -- 6. AMOUNT-BASED PERMISSION OVERRIDE
        -- =====================================================================
        SET @EffectivePermissionId = @WorkflowStagePermission;

        -- Try to get amount from source table
        BEGIN TRY
            DECLARE @AmountSQL NVARCHAR(MAX);
            SET @TableName = PARSENAME(@Source, 1);
            SET @KeyColumn = 'Id';

            -- Find key column
            SELECT TOP 1 @KeyColumn = COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
            WHERE TABLE_NAME = @TableName
              AND COLUMN_NAME IN ('Id', 'ID', 'PlanID');

            IF @KeyColumn IS NULL
                SET @KeyColumn = 'Id';

            -- Check if Amount column exists
            IF EXISTS (
                SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'Amount'
            )
            BEGIN
                SET @AmountSQL = N'SELECT @AmountOut = ISNULL(Amount, 0) FROM ' +
                    QUOTENAME(@TableName) + ' WHERE ' + QUOTENAME(@KeyColumn) +
                    ' = TRY_CAST(@SourceID AS BIGINT)';

                EXEC sp_executesql @AmountSQL,
                    N'@SourceID NVARCHAR(100), @AmountOut DECIMAL(20,4) OUTPUT',
                    @SourceID, @Amount OUTPUT;
            END
        END TRY
        BEGIN CATCH
            SET @Amount = 0;
        END CATCH

        -- Override permission based on amount limits if configured
        IF @StageIDAsInt IS NOT NULL
            AND EXISTS (SELECT 1 FROM t_WorkFlowLimits WITH (NOLOCK)
                        WHERE WorkFlowStageId = @StageIDAsInt AND DeletedOn IS NULL)
        BEGIN
            SELECT TOP 1 @EffectivePermissionId = PermissionId
            FROM t_WorkFlowLimits WITH (NOLOCK)
            WHERE WorkFlowStageId = @StageIDAsInt
              AND DeletedOn IS NULL
              AND @Amount <= MaxAmount
            ORDER BY MaxAmount ASC;

            -- Fallback to highest limit if amount exceeds all
            IF @EffectivePermissionId IS NULL
            BEGIN
                SELECT TOP 1 @EffectivePermissionId = PermissionId
                FROM t_WorkFlowLimits WITH (NOLOCK)
                WHERE WorkFlowStageId = @StageIDAsInt AND DeletedOn IS NULL
                ORDER BY MaxAmount DESC;
            END

            -- Final fallback to stage default
            IF @EffectivePermissionId IS NULL
                SET @EffectivePermissionId = @WorkflowStagePermission;
        END

        -- =====================================================================
        -- 7. CHECK USER PERMISSIONS
        -- =====================================================================
        SET @UserHasPermissions = dbo.f_CheckUserPermission(@UserID, @EffectivePermissionId);

        IF @UserHasPermissions = 0
        BEGIN
            RAISERROR('User does not have required permissions for this workflow stage', 16, 1);
            RETURN;
        END

        -- =====================================================================
        -- 8. DUPLICATE ACTION PREVENTION
        -- =====================================================================
        IF EXISTS (
            SELECT 1 FROM t_WorkFlowHistory WITH (NOLOCK)
            WHERE Source = @Source
              AND SourceID = @SourceID
              AND Stage = @StageID
              AND CreatedBy = @UserID
              AND StatusId = @StatusID
              AND DeletedOn IS NULL
        )
        BEGIN
            RAISERROR('You have already acted on this approval', 16, 1);
            RETURN;
        END

        -- =====================================================================
        -- 9. COUNT PENDING BEFORE ACTION
        -- =====================================================================
        SELECT @PendingCountBeforeDelete = COUNT(*)
        FROM dbo.t_WorkFlowPending WITH (NOLOCK)
        WHERE Source = @Source
          AND SourceID = @SourceID
          AND Stage = @StageID
          AND DeletedOn IS NULL;

        -- =====================================================================
        -- 10. SOFT-DELETE USER'S PENDING RECORD (BEFORE checking remaining)
        -- =====================================================================
        UPDATE dbo.t_WorkFlowPending
        SET DeletedBy = @UserID,
            DeletedOn = GETDATE(),
            ModifiedBy = @UserID,
            ModifiedOn = GETDATE()
        WHERE Source = @Source
          AND SourceID = @SourceID
          AND UserId = @UserID
          AND DeletedOn IS NULL;

        IF @@ROWCOUNT = 0
        BEGIN
            RAISERROR('Failed to process pending approval entry', 16, 1);
            RETURN;
        END

        -- =====================================================================
        -- 11. RECORD ACTION IN HISTORY
        -- =====================================================================
        INSERT INTO dbo.t_WorkFlowHistory (
            Source, SourceID, Stage, Notes, StatusId,
            CreatedBy, CreatedOn, ModifiedBy, ModifiedOn, IsApproved
        )
        VALUES (
            @Source, @SourceID, @StageID, @Notes, @StatusID,
            @UserID, GETDATE(), @UserID, GETDATE(), @IsApproved
        );

        -- =====================================================================
        -- 12. HANDLE REJECTION — clear all pending, update source, return
        -- =====================================================================
        IF @IsRejectionAction = 1
        BEGIN
            -- Get system user for cleanup
            SELECT TOP 1 @SystemUserId = Id
            FROM t_Users WITH (NOLOCK)
            WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

            IF @SystemUserId IS NULL
                SET @SystemUserId = @UserID;

            -- Soft-delete ALL remaining pending records across all stages
            UPDATE dbo.t_WorkFlowPending
            SET DeletedBy = @SystemUserId,
                DeletedOn = GETDATE(),
                ModifiedBy = @SystemUserId,
                ModifiedOn = GETDATE()
            WHERE Source = @Source
              AND SourceID = @SourceID
              AND DeletedOn IS NULL;

            -- Update source table with rejected status
            SET @TableName = PARSENAME(@Source, 1);

            IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = @TableName)
            BEGIN
                -- Reset key column
                SET @KeyColumn = 'Id';
                SELECT TOP 1 @KeyColumn = COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                WHERE TABLE_NAME = @TableName AND COLUMN_NAME IN ('Id', 'ID');

                IF @KeyColumn IS NULL SET @KeyColumn = 'Id';

                -- Validate required columns exist
                IF EXISTS (
                    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                    WHERE TABLE_NAME = @TableName AND COLUMN_NAME = @StatusColumn
                )
                AND EXISTS (
                    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                    WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'ModifiedBy'
                )
                AND EXISTS (
                    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                    WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'ModifiedOn'
                )
                BEGIN
                    SET @LastApproverColumn = '';
                    IF EXISTS (
                        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                        WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'LastApprover'
                    )
                    BEGIN
                        SET @LastApproverColumn = ', LastApprover = @UserName';
                    END

                    SET @UpdateSQL = N'UPDATE ' + QUOTENAME(@TableName) + ' SET ' +
                        QUOTENAME(@StatusColumn) + ' = @StatusID, ' +
                        'ModifiedBy = @UserID, ModifiedOn = GETDATE()' +
                        @LastApproverColumn +
                        ' WHERE ' + QUOTENAME(@KeyColumn) + ' = @SourceID';

                    EXEC sp_executesql @UpdateSQL,
                        N'@StatusID BIGINT, @UserName NVARCHAR(255), @SourceID NVARCHAR(100), @UserID BIGINT',
                        @StatusID, @UserName, @SourceID, @UserID;
                END
            END

            COMMIT TRANSACTION;

            -- Email notification for rejection
            SELECT @UserEmail = Email FROM t_Users WITH (NOLOCK) WHERE Id = @UserID;
            IF @UserEmail IS NOT NULL AND LEN(@UserEmail) > 5
            BEGIN
                SET @EmailSubject = 'Workflow Rejection - ' + ISNULL(@Description, '[Unknown]');
                SET @EmailMessage =
                    'Workflow for Source: ' + ISNULL(@Source, '[Unknown]') +
                    ', ID: ' + ISNULL(@SourceID, '[Unknown]') +
                    ' has been rejected. Reason: ' + ISNULL(@Notes, '[No notes provided]');

                EXEC p_sendNotificationEmail
                    @UserID = @UserID,
                    @Subject = @EmailSubject,
                    @Message = @EmailMessage,
                    @SenderId = @UserID,
                    @Source = @Source,
                    @SourceID = @SourceID;
            END

            SELECT 'SUCCESS' AS Status,
                   @Description + ' Recorded' AS Message,
                   @Description AS WorkflowStatus,
                   CAST(1) AS StageCompleted,
                   0 AS CurrentApprovals,
                   0 AS RequiredApprovals;
            RETURN;
        END

        -- =====================================================================
        -- 13. HANDLE APPROVAL — multi-approval logic
        -- =====================================================================
        IF @IsApprovalAction = 1
        BEGIN
            -- Count approvals already recorded for this stage
            SELECT @CurrentApprovedCount = COUNT(*)
            FROM t_WorkFlowHistory WITH (NOLOCK)
            WHERE Source = @Source
              AND SourceID = @SourceID
              AND Stage = @StageID
              AND IsApproved = 1
              AND DeletedOn IS NULL;

            -- Determine total approvals required based on workflow type
            SET @WorkflowType = ISNULL(@WorkflowType, 'CNT');

            IF @WorkflowType = 'ALL'
            BEGIN
                -- All eligible users must approve
                SELECT @TotalEligibleUsers = COUNT(*)
                FROM dbo.f_getUserWithPermission(@EffectivePermissionId);

                SET @TotalApprovalsRequired = @TotalEligibleUsers;
            END
            ELSE IF @WorkflowType = 'MAJ'
            BEGIN
                -- Majority must approve
                SELECT @TotalEligibleUsers = COUNT(*)
                FROM dbo.f_getUserWithPermission(@EffectivePermissionId);

                SET @TotalApprovalsRequired = (@TotalEligibleUsers / 2) + 1;
            END
            ELSE -- CNT or AMT or default
            BEGIN
                SET @TotalApprovalsRequired = @ConfiguredCount;
            END

            -- Ensure at least 1 approval is required
            IF @TotalApprovalsRequired < 1
                SET @TotalApprovalsRequired = 1;

            -- Check if stage is complete
            IF @CurrentApprovedCount >= @TotalApprovalsRequired
            BEGIN
                SET @StageCompleted = 1;

                -- Get system user for cleanup
                SELECT TOP 1 @SystemUserId = Id
                FROM t_Users WITH (NOLOCK)
                WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

                IF @SystemUserId IS NULL
                    SET @SystemUserId = @UserID;

                -- Clean up remaining pending records for this stage
                UPDATE dbo.t_WorkFlowPending
                SET DeletedBy = @SystemUserId,
                    DeletedOn = GETDATE(),
                    ModifiedBy = @SystemUserId,
                    ModifiedOn = GETDATE()
                WHERE Source = @Source
                  AND SourceID = @SourceID
                  AND Stage = @StageID
                  AND DeletedOn IS NULL;
            END
        END

        -- =====================================================================
        -- 14. CHECK FOR REMAINING PENDING APPROVALS (any stage)
        -- =====================================================================
        SELECT @HasPendingApprovals = CASE WHEN EXISTS (
            SELECT 1 FROM dbo.t_WorkFlowPending WITH (NOLOCK)
            WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL
        ) THEN 1 ELSE 0 END;

        -- =====================================================================
        -- 15. UPDATE SOURCE TABLE (only if no more pending approvals)
        -- =====================================================================
        IF @HasPendingApprovals = 0
        BEGIN
            SET @TableName = PARSENAME(@Source, 1);

            IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = @TableName)
            BEGIN
                -- Reset key column
                SET @KeyColumn = 'Id';
                SELECT TOP 1 @KeyColumn = COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                WHERE TABLE_NAME = @TableName AND COLUMN_NAME IN ('Id', 'ID');

                IF @KeyColumn IS NULL SET @KeyColumn = 'Id';

                -- Validate required columns
                IF EXISTS (
                    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                    WHERE TABLE_NAME = @TableName AND COLUMN_NAME = @StatusColumn
                )
                AND EXISTS (
                    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                    WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'ModifiedBy'
                )
                AND EXISTS (
                    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                    WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'ModifiedOn'
                )
                BEGIN
                    SET @LastApproverColumn = '';
                    IF EXISTS (
                        SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                        WHERE TABLE_NAME = @TableName AND COLUMN_NAME = 'LastApprover'
                    )
                    BEGIN
                        SET @LastApproverColumn = ', LastApprover = @UserName';
                    END

                    SET @UpdateSQL = N'UPDATE ' + QUOTENAME(@TableName) + ' SET ' +
                        QUOTENAME(@StatusColumn) + ' = @StatusID, ' +
                        'ModifiedBy = @UserID, ModifiedOn = GETDATE()' +
                        @LastApproverColumn +
                        ' WHERE ' + QUOTENAME(@KeyColumn) + ' = @SourceID';

                    EXEC sp_executesql @UpdateSQL,
                        N'@StatusID BIGINT, @UserName NVARCHAR(255), @SourceID NVARCHAR(100), @UserID BIGINT',
                        @StatusID, @UserName, @SourceID, @UserID;
                END
                ELSE
                BEGIN
                    SET @ErrorMessage = 'Required columns missing in table: ' + @TableName +
                        '. Need: ' + @StatusColumn + ', ModifiedBy, ModifiedOn';
                    RAISERROR(@ErrorMessage, 16, 1);
                    RETURN;
                END
            END
            ELSE
            BEGIN
                SET @ErrorMessage = 'Source table does not exist: ' + @Source;
                RAISERROR(@ErrorMessage, 16, 1);
                RETURN;
            END
        END

        COMMIT TRANSACTION;

        -- =====================================================================
        -- 16. EMAIL NOTIFICATION (outside transaction)
        -- =====================================================================
        SELECT @UserEmail = Email FROM t_Users WITH (NOLOCK) WHERE Id = @UserID;

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

        -- =====================================================================
        -- 17. RETURN SUCCESS
        -- =====================================================================
        SELECT 'SUCCESS' AS Status,
               @Description + ' Recorded' AS Message,
               @Description AS WorkflowStatus,
               @StageCompleted AS StageCompleted,
               @CurrentApprovedCount AS CurrentApprovals,
               @TotalApprovalsRequired AS RequiredApprovals;

    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        SELECT
            @ErrorMessage = ERROR_MESSAGE(),
            @ErrorSeverity = ERROR_SEVERITY(),
            @ErrorState = ERROR_STATE();

        RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
    END CATCH
END
GO