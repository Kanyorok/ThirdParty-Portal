/****** Object:  StoredProcedure [dbo].[p_ProcessWorkflowAction]    Script Date: 29/12/2025 12:55:26 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER   PROCEDURE [dbo].[p_ProcessWorkflowAction]
    @Source NVARCHAR(255),
    @SourceID NVARCHAR(100),
    @UserID BIGINT,
    @UserName NVARCHAR(255) = NULL,
    @Notes NVARCHAR(MAX) = NULL,
    @StatusColumn NVARCHAR(100) = 'Status',
    @StatusID BIGINT,
    @StatusValueToSet NVARCHAR(50) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE
        @StageID NVARCHAR(200),
        @StageIDAsInt BIGINT,
        @WorkFlowID BIGINT,
        @WorkflowStagePermission BIGINT,
        @ConfiguredCount INT = 1,
        @TotalApprovalsRequired INT = 0,
        @CurrentApprovedCount INT = 0,
        @SubmitterId BIGINT,
        @permissionName NVARCHAR(100),
        @UserHasPermissions SMALLINT = 0,
        @StatusValue NVARCHAR(50),
        @Description NVARCHAR(100),
        @HasPendingApprovals BIT = 0,
        @StageCompleted BIT = 0,
        @SystemUserId BIGINT,
        @WorkflowType NVARCHAR(100),
        @StageName NVARCHAR(255),
        @RemainingPendingCount INT = 0,
        @PendingCountBEFOREDelete INT = 0,
        @TotalEligibleUsers INT = 0,
        @Amount DECIMAL(20,4) = 0,
        @EffectivePermissionId BIGINT,
        @IsApprovalAction BIT = 0,
        @IsRejectionAction BIT = 0,
        @UserEmail NVARCHAR(255),
        @EmailMessage NVARCHAR(MAX),
        @EmailSubject NVARCHAR(255);

    PRINT 'p_ProcessWorkflowAction STARTED';
    PRINT 'Source: ' + @Source + ', SourceID: ' + @SourceID;
    PRINT 'UserID: ' + CAST(@UserID AS NVARCHAR(50)) + ', StatusID: ' + CAST(@StatusID AS NVARCHAR(50));

    -- Get system user
    SELECT TOP 1 @SystemUserId = Id FROM t_Users WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

    -- Determine if this is an approval or rejection based on the Description
    SELECT TOP 1 @StatusValue = Value, @Description = Description
    FROM t_CodeDetails WHERE ID = @StatusID;

    IF @Description IS NULL
    BEGIN
        SELECT 'ERROR' AS Status, 'Invalid StatusID' AS Message;
        RETURN;
    END

    -- Set flags based on description (case-insensitive)
    IF LOWER(@Description) LIKE '%approve%' OR LOWER(@Description) = 'approval'
        SET @IsApprovalAction = 1;
    ELSE IF LOWER(@Description) LIKE '%reject%'
        SET @IsRejectionAction = 1;

    PRINT 'StatusID: ' + CAST(@StatusID AS NVARCHAR(10)) + ', Description: ' + @Description;
    PRINT 'IsApproval: ' + CAST(@IsApprovalAction AS NVARCHAR(1)) + ', IsRejection: ' + CAST(@IsRejectionAction AS NVARCHAR(1));

    -- Maker-checker rule
    SELECT TOP 1 @SubmitterId = CreatedBy
    FROM t_WorkFlowHistory
    WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL
    ORDER BY CreatedOn ASC;

    IF @SubmitterId = @UserID
    BEGIN
        SELECT 'ERROR' AS Status, 'Cannot approve your own submission (maker-checker rule)' AS Message;
        RETURN;
    END

    -- Get workflow stage info from pending
    SELECT TOP 1
        @StageID = p.Stage,
        @WorkFlowID = ws.WorkFlowID,
        @WorkflowStagePermission = ws.PermissionId,
        @ConfiguredCount = ISNULL(ws.Count, 1),
        @StageName = ws.StageName,
        @WorkflowType = wt.TypeID
    FROM dbo.t_WorkFlowPending p
    JOIN dbo.t_WorkFlowStages ws ON p.Stage = CAST(ws.Id AS NVARCHAR(200))
    LEFT JOIN dbo.t_WorkFlowTypes wt ON ws.WorkFlowTypeId = wt.Id
    WHERE p.Source = @Source
      AND p.SourceID = @SourceID
      AND p.UserId = @UserID
      AND p.DeletedOn IS NULL;

    IF @StageID IS NULL
    BEGIN
        SELECT 'ERROR' AS Status, 'No pending approval found for this user or already actioned' AS Message;
        RETURN;
    END

    SET @StageIDAsInt = CAST(@StageID AS BIGINT);

    PRINT '';
    PRINT 'STAGE INFO:';
    PRINT '  StageID: ' + @StageID + ' | StageName: ' + ISNULL(@StageName, 'N/A');
    PRINT '  WorkflowType: ' + ISNULL(@WorkflowType, 'NULL') + ' | ConfiguredCount: ' + CAST(@ConfiguredCount AS NVARCHAR(10));

    -- GET AMOUNT AND EFFECTIVE PERMISSION
    SET @EffectivePermissionId = @WorkflowStagePermission;

    BEGIN TRY
        DECLARE @sql NVARCHAR(MAX) = N'
            SELECT @AmountOut = ISNULL(Amount, 0)
            FROM ' + QUOTENAME(@Source) + '
            WHERE ' + CASE
                WHEN @Source = 't_ConsolidatedProcurementPlan' THEN 'PlanID'
                ELSE 'Id'
            END + ' = CAST(@SourceID AS BIGINT)';
        EXEC sp_executesql @sql,
            N'@SourceID NVARCHAR(100), @AmountOut DECIMAL(20,4) OUTPUT',
            @SourceID, @Amount OUTPUT;
    END TRY
    BEGIN CATCH
        SET @Amount = 0;
    END CATCH

    -- Amount-based permission selection
    IF EXISTS (SELECT 1 FROM t_WorkFlowLimits WHERE WorkFlowStageId = @StageIDAsInt AND DeletedOn IS NULL)
    BEGIN
        SELECT TOP 1 @EffectivePermissionId = PermissionId
        FROM t_WorkFlowLimits
        WHERE WorkFlowStageId = @StageIDAsInt AND DeletedOn IS NULL AND @Amount <= MaxAmount
        ORDER BY MaxAmount ASC;

        IF @EffectivePermissionId IS NULL
        BEGIN
            SELECT TOP 1 @EffectivePermissionId = PermissionId
            FROM t_WorkFlowLimits WHERE WorkFlowStageId = @StageIDAsInt AND DeletedOn IS NULL
            ORDER BY MaxAmount DESC;
        END

        IF @EffectivePermissionId IS NULL
            SET @EffectivePermissionId = @WorkflowStagePermission;

        PRINT '  Amount: ' + CAST(@Amount AS NVARCHAR(30)) + ' | EffectivePermission: ' + CAST(@EffectivePermissionId AS NVARCHAR(20));
    END

    -- PERMISSION CHECK
    SELECT TOP 1 @permissionName = name FROM t_Permissions WHERE id = @EffectivePermissionId;

    IF @permissionName IS NOT NULL
    BEGIN
        SELECT @UserHasPermissions = CASE
            WHEN EXISTS (
                SELECT 1 FROM [t_Users] u
                WHERE u.Id = @UserID AND u.DeletedOn IS NULL
                  AND EXISTS (
                      SELECT 1 FROM [t_ModelRoles] mr
                      INNER JOIN [t_RolePermissions] rp ON mr.role_id = rp.role_id
                      INNER JOIN [t_Permissions] p ON rp.permission_id = p.id
                      WHERE mr.model_id = u.Id AND mr.model_type = 'UserID' AND p.name = @permissionName
                  )
            ) THEN 1 ELSE 0 END;
    END

    IF @UserHasPermissions = 0
    BEGIN
        SELECT 'ERROR' AS Status, 'User does not have required permissions' AS Message;
        RETURN;
    END

    -- Prevent duplicate actions using the ACTUAL StatusID passed
    IF EXISTS (
        SELECT 1 FROM t_WorkFlowHistory
        WHERE Source = @Source AND SourceID = @SourceID
          AND Stage = @StageID AND CreatedBy = @UserID
          AND StatusId = @StatusID
          AND DeletedOn IS NULL
    )
    BEGIN
        SELECT 'ERROR' AS Status, 'You have already acted on this approval' AS Message;
        RETURN;
    END

    -- Count pending BEFORE delete
    SELECT @PendingCountBEFOREDelete = COUNT(*)
    FROM dbo.t_WorkFlowPending
    WHERE Source = @Source AND SourceID = @SourceID AND Stage = @StageID AND DeletedOn IS NULL;

    PRINT '';
    PRINT 'BEFORE ACTION: Pending entries: ' + CAST(@PendingCountBEFOREDelete AS NVARCHAR(10));

    -- Mark user's pending as deleted
    UPDATE dbo.t_WorkFlowPending
    SET DeletedBy = @UserID, DeletedOn = GETDATE(), ModifiedBy = @UserID, ModifiedOn = GETDATE()
    WHERE Source = @Source AND SourceID = @SourceID AND Stage = @StageID
      AND UserId = @UserID AND DeletedOn IS NULL;

    IF @@ROWCOUNT = 0
    BEGIN
        SELECT 'ERROR' AS Status, 'Failed to delete pending entry' AS Message;
        RETURN;
    END

    PRINT '   Action: ' + @Description;

    -- Record action in history
    INSERT INTO dbo.t_WorkFlowHistory (
        Source, SourceID, Notes, StatusId,
        CreatedBy, CreatedOn, ModifiedBy, ModifiedOn, Stage, isApproved
    )
    VALUES (
        @Source, @SourceID, @Notes, @StatusID,
        @UserID, GETDATE(), @UserID, GETDATE(), @StageID,
        CASE WHEN @IsApprovalAction = 1 THEN 1
             WHEN @IsRejectionAction = 1 THEN 2
             ELSE NULL END
    );

    PRINT '   History entry created';

    -- ========================================
    -- HANDLE REJECTION
    -- ========================================
    IF @IsRejectionAction = 1
    BEGIN
        PRINT '';
        PRINT '=== PROCESSING REJECTION ===';

        -- Delete all remaining pending entries for this workflow
        UPDATE dbo.t_WorkFlowPending
        SET DeletedBy = @UserID, DeletedOn = GETDATE(), ModifiedBy = @UserID, ModifiedOn = GETDATE()
        WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL;

        -- FIX: Use the VALUE (not ID) to update the status column
        DECLARE @RejectedStatusValue NVARCHAR(50) = COALESCE(@StatusValueToSet, @StatusValue);

        PRINT '  Updating source table status to: ' + @RejectedStatusValue;

        DECLARE @UpdateRejectSQL NVARCHAR(MAX) = N'
            UPDATE ' + QUOTENAME(@Source) + '
            SET ' + QUOTENAME(@StatusColumn) + ' = @FinalStatusValue,
                ModifiedBy = @UserID,
                ModifiedOn = GETDATE()
            WHERE ' + CASE
                WHEN @Source = 't_ConsolidatedProcurementPlan' THEN 'PlanID'
                ELSE 'Id'
            END + ' = CAST(@SourceID AS BIGINT)';

        EXEC sp_executesql @UpdateRejectSQL,
            N'@FinalStatusValue NVARCHAR(50), @SourceID NVARCHAR(100), @UserID BIGINT',
            @RejectedStatusValue, @SourceID, @UserID;

        PRINT '  Source table updated successfully';

        -- Send email confirmation
        SELECT @UserEmail = Email FROM t_Users WHERE Id = @UserID;
        IF @UserEmail IS NOT NULL
        BEGIN
            SET @EmailSubject = 'Workflow Action Confirmation - ' + @Description;
            SET @EmailMessage = 'Your workflow action for Source: ' + @Source + ', ID: ' + @SourceID +
                               ' has been recorded as: ' + @Description + '.';

            EXEC p_sendNotificationEmail
                 @UserID = @UserID,
                 @Subject = @EmailSubject,
                 @Message = @EmailMessage,
                 @SenderId = @UserID,
                 @Source = @Source,
                 @SourceID = @SourceID;

            PRINT '  Email confirmation sent';
        END

        SELECT 'SUCCESS' AS Status,
               'Workflow rejected successfully' AS Message,
               'Rejected' AS WorkflowStatus,
               0 AS StageCompleted;
        RETURN;
    END

    -- ========================================
    -- HANDLE APPROVAL
    -- ========================================
    IF @IsApprovalAction = 1
    BEGIN
        PRINT '';
        PRINT '=== PROCESSING APPROVAL ===';

        SELECT @RemainingPendingCount = COUNT(*)
        FROM dbo.t_WorkFlowPending
        WHERE Source = @Source AND SourceID = @SourceID AND Stage = @StageID AND DeletedOn IS NULL;

        -- Count approvals using the ACTUAL StatusID passed
        SELECT @CurrentApprovedCount = COUNT(DISTINCT CreatedBy)
        FROM dbo.t_WorkFlowHistory
        WHERE Source = @Source AND SourceID = @SourceID AND Stage = @StageID
          AND StatusId = @StatusID
          AND DeletedOn IS NULL;

        SELECT @TotalEligibleUsers = COUNT(DISTINCT u.Id)
        FROM t_Users u
        WHERE u.DeletedOn IS NULL
          AND EXISTS (SELECT 1 FROM dbo.f_getUserWithPermission(@EffectivePermissionId) WHERE Id = u.Id)
          AND u.Id <> ISNULL(@SubmitterId, 0);

        PRINT 'COUNTS:';
        PRINT '  Remaining pending: ' + CAST(@RemainingPendingCount AS NVARCHAR(10));
        PRINT '  Current approved: ' + CAST(@CurrentApprovedCount AS NVARCHAR(10));
        PRINT '  Total eligible: ' + CAST(@TotalEligibleUsers AS NVARCHAR(10));

        -- DETERMINE REQUIRED APPROVALS BY TYPE
        IF @WorkflowType = 'ALL'
        BEGIN
            SET @TotalApprovalsRequired = @TotalEligibleUsers;
            PRINT '  Type ALL: Requires all ' + CAST(@TotalApprovalsRequired AS NVARCHAR(10));
        END
        ELSE IF @WorkflowType = 'MAJ'
        BEGIN
            SET @TotalApprovalsRequired = (@TotalEligibleUsers / 2) + 1;
            PRINT '  Type MAJ: Requires ' + CAST(@TotalApprovalsRequired AS NVARCHAR(10)) + ' of ' + CAST(@TotalEligibleUsers AS NVARCHAR(10));
        END
        ELSE IF @WorkflowType = 'CNT' OR @WorkflowType = 'AMT'
        BEGIN
            SET @TotalApprovalsRequired = @ConfiguredCount;
            PRINT '  Type ' + @WorkflowType + ': Requires ' + CAST(@TotalApprovalsRequired AS NVARCHAR(10));
        END
        ELSE
        BEGIN
            SET @TotalApprovalsRequired = @ConfiguredCount;
            PRINT '  Type UNKNOWN: Requires ' + CAST(@TotalApprovalsRequired AS NVARCHAR(10));
        END

        PRINT '';
        PRINT 'COMPLETION CHECK:';
        PRINT '  Approved (' + CAST(@CurrentApprovedCount AS NVARCHAR(10)) + ') >= Required (' + CAST(@TotalApprovalsRequired AS NVARCHAR(10)) + ')? ' +
              CASE WHEN @CurrentApprovedCount >= @TotalApprovalsRequired THEN 'YES ✓' ELSE 'NO ✗' END;

        -- Check if stage is complete
        IF @CurrentApprovedCount >= @TotalApprovalsRequired
        BEGIN
            SET @StageCompleted = 1;
            PRINT '';
            PRINT 'STAGE COMPLETE!';

            -- Delete remaining pending for this stage
            UPDATE dbo.t_WorkFlowPending
            SET DeletedBy = @SystemUserId, DeletedOn = GETDATE(), ModifiedBy = @SystemUserId, ModifiedOn = GETDATE()
            WHERE Source = @Source AND SourceID = @SourceID AND Stage = @StageID AND DeletedOn IS NULL;

            -- Mark history entries as approved
            UPDATE t_WorkFlowHistory
            SET isApproved = 1, ModifiedBy = @UserID, ModifiedOn = GETDATE()
            WHERE Source = @Source AND SourceID = @SourceID AND Stage = @StageID
              AND StatusId = @StatusID
              AND DeletedOn IS NULL;
        END
        ELSE
        BEGIN
            PRINT '';
            PRINT '  Stage NOT complete - waiting for more approvals';
        END
    END

    -- Check for pending in ANY stage
    SELECT @HasPendingApprovals = CASE
        WHEN EXISTS (
            SELECT 1 FROM dbo.t_WorkFlowPending
            WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL
        ) THEN 1 ELSE 0 END;

    PRINT '';
    PRINT 'Pending in ANY stage: ' + CAST(@HasPendingApprovals AS NVARCHAR(10));
    PRINT '';
    PRINT 'FINAL: StageCompleted=' + CAST(@StageCompleted AS NVARCHAR(10)) + ', HasPending=' + CAST(@HasPendingApprovals AS NVARCHAR(10));

    -- NOTE: Source table status update removed - PHP will handle it via advanceToNextStage()

    -- Send email confirmation for approval action
    SELECT @UserEmail = Email FROM t_Users WHERE Id = @UserID;
    IF @UserEmail IS NOT NULL AND @IsApprovalAction = 1
    BEGIN
        SET @EmailSubject = 'Workflow Action Confirmation - ' + @Description;
        SET @EmailMessage = 'Your workflow action for Source: ' + @Source + ', ID: ' + @SourceID +
                           ' has been recorded as: ' + @Description + '.';

        -- Add stage completion info to email
        IF @StageCompleted = 1
        BEGIN
            SET @EmailMessage = @EmailMessage + ' The current stage has been completed.';
        END
        ELSE
        BEGIN
            SET @EmailMessage = @EmailMessage + ' The current stage requires more approvals.';
        END

        EXEC p_sendNotificationEmail
             @UserID = @UserID,
             @Subject = @EmailSubject,
             @Message = @EmailMessage,
             @SenderId = @UserID,
             @Source = @Source,
             @SourceID = @SourceID;

        PRINT 'Email confirmation sent to user: ' + CAST(@UserID AS NVARCHAR(20));
    END

    -- Return results
    SELECT
        'SUCCESS' AS Status,
        CASE
            WHEN @HasPendingApprovals = 1 AND @StageCompleted = 0 THEN 'Approval recorded, awaiting other approvers in this stage'
            WHEN @HasPendingApprovals = 1 AND @StageCompleted = 1 THEN 'Stage completed, ready for next stage'
            WHEN @HasPendingApprovals = 0 AND @StageCompleted = 1 THEN 'Stage completed, ready for next stage'
            ELSE @Description + ' recorded'
        END AS Message,
        CASE
            WHEN @HasPendingApprovals = 1 THEN 'Pending'
            WHEN @IsApprovalAction = 1 THEN 'Approved'
            WHEN @IsRejectionAction = 1 THEN 'Rejected'
            ELSE 'Unknown'
        END AS WorkflowStatus,
        @StageCompleted AS StageCompleted,
        @CurrentApprovedCount AS CurrentApprovals,
        @TotalApprovalsRequired AS RequiredApprovals;
END;
