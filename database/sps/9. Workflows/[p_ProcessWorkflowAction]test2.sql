
CREATE or ALTER   PROCEDURE [dbo].[p_ProcessWorkflowAction]
    @Source NVARCHAR(255),
    @SourceID NVARCHAR(100),
    @UserID BIGINT,
    @UserName NVARCHAR(255) = NULL,
    @Notes NVARCHAR(MAX) = NULL,
    @StatusColumn NVARCHAR(100) = 'Status',
    @StatusID BIGINT,
    @StatusValueToSet NVARCHAR(50) = NULL,
    @Amount DECIMAL(20,4) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    -- ==========================================================================================
    -- RESTRICTION: CSADM user provided check
    -- Prevent CSADM from performing any workflow actions
    -- ==========================================================================================
    IF EXISTS (SELECT 1 FROM t_Users WHERE Id = @UserID AND UserID = 'CSADM')
    BEGIN
        SELECT 'ERROR' AS Status, 'The CSADM user is restricted from performing workflow actions.' AS Message;
        RETURN;
    END
    -- ==========================================================================================

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
        @EffectivePermissionId BIGINT,
        @IsApprovalAction BIT = 0,
        @IsRejectionAction BIT = 0,
        @UserEmail NVARCHAR(255),
        @EmailMessage NVARCHAR(MAX),
        @EmailSubject NVARCHAR(255),
        @LimitType NVARCHAR(20) = 'DEFAULT',
        @TierMaxAmount DECIMAL(20,4) = NULL;

    PRINT 'p_ProcessWorkflowAction STARTED';
    PRINT 'Source: ' + @Source + ', SourceID: ' + @SourceID;
    PRINT 'UserID: ' + CAST(@UserID AS NVARCHAR(50)) + ', StatusID: ' + CAST(@StatusID AS NVARCHAR(50));
    PRINT 'Amount: ' + ISNULL(CAST(@Amount AS NVARCHAR(30)), 'NOT PROVIDED (non-AMT workflow)');

    -- Get system user
    SELECT TOP 1 @SystemUserId = Id FROM t_Users WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

    -- Determine if this is an approval or rejection
    SELECT TOP 1 @StatusValue = Value, @Description = Description 
    FROM t_CodeDetails WHERE ID = @StatusID;

    IF @Description IS NULL
    BEGIN
        SELECT 'ERROR' AS Status, 'Invalid StatusID' AS Message;
        RETURN;
    END

    -- Set flags based on description
    IF LOWER(@Description) LIKE '%approve%' OR LOWER(@Description) = 'approval'
        SET @IsApprovalAction = 1;
    ELSE IF LOWER(@Description) LIKE '%reject%'
        SET @IsRejectionAction = 1;

    PRINT 'Action: ' + @Description + ' (IsApproval: ' + CAST(@IsApprovalAction AS NVARCHAR(1)) + ', IsRejection: ' + CAST(@IsRejectionAction AS NVARCHAR(1)) + ')';

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

    -- ========================================
    -- CRITICAL: GET STAGE INFO AND CHECK PENDING FIRST
    -- ========================================
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

    -- ❌ CRITICAL CHECK: User must have a pending record for THIS stage
    IF @StageID IS NULL
    BEGIN
        SELECT 'ERROR' AS Status, 
               'No pending approval found for this user. You may have already acted on this, or you do not have permission to approve this stage.' AS Message;
        RETURN;
    END

    SET @StageIDAsInt = CAST(@StageID AS BIGINT);

    -- ========================================
    -- GET EFFECTIVE PERMISSION (FOR AMT TIERS)
    -- ========================================
    SET @EffectivePermissionId = @WorkflowStagePermission;

    IF @WorkflowType = 'AMT' AND @Amount IS NOT NULL AND @Amount > 0
    BEGIN
        IF EXISTS (
            SELECT 1 FROM t_WorkFlowLimits 
            WHERE WorkFlowStageId = @StageIDAsInt AND DeletedOn IS NULL
        )
        BEGIN
            SELECT TOP 1 
                @EffectivePermissionId = PermissionId,
                @TierMaxAmount = MaxAmount,
                @LimitType = LimitType
            FROM dbo.f_getPermissionForAmount(@StageIDAsInt, @Amount);

            IF @EffectivePermissionId IS NULL
                SET @EffectivePermissionId = @WorkflowStagePermission;
        END
    END

    -- ========================================
    -- ✅ CRITICAL: VERIFY USER HAS THE REQUIRED PERMISSION
    -- ========================================
    SELECT TOP 1 @permissionName = name 
    FROM t_Permissions 
    WHERE id = @EffectivePermissionId;

    -- Check if user has this specific permission
    IF @EffectivePermissionId IS NOT NULL
    BEGIN
        SELECT @UserHasPermissions = CASE 
            WHEN EXISTS (
                SELECT 1 FROM dbo.f_getUserWithPermission(@EffectivePermissionId)
                WHERE Id = @UserID
            ) THEN 1 ELSE 0 END;
    END

    -- ❌ BLOCK if user doesn't have permission
    IF @UserHasPermissions = 0
    BEGIN
        -- Remove the invalid pending entry (shouldn't exist)
        UPDATE dbo.t_WorkFlowPending
        SET DeletedBy = @SystemUserId, 
            DeletedOn = GETDATE(), 
            ModifiedBy = @SystemUserId, 
            ModifiedOn = GETDATE()
        WHERE Source = @Source 
          AND SourceID = @SourceID 
          AND Stage = @StageID
          AND UserId = @UserID 
          AND DeletedOn IS NULL;
        
        SELECT 'ERROR' AS Status, 
               'Permission Denied: You do not have the required permission to approve this stage. Required: ' + 
               ISNULL(@permissionName, 'Unknown') + ' (ID: ' + CAST(@EffectivePermissionId AS NVARCHAR(20)) + ')' AS Message;
        RETURN;
    END

    -- ========================================
    -- PREVENT DUPLICATE ACTIONS
    -- ========================================
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

    -- ========================================
    -- HANDLE REJECTION
    -- ========================================
    IF @IsRejectionAction = 1
    BEGIN
        -- Delete all remaining pending entries
        UPDATE dbo.t_WorkFlowPending
        SET DeletedBy = @UserID, DeletedOn = GETDATE(), ModifiedBy = @UserID, ModifiedOn = GETDATE()
        WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL;

        DECLARE @RejectedStatusValue NVARCHAR(50) = COALESCE(@StatusValueToSet, @StatusValue);
        
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

        -- Send email
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
        SELECT @RemainingPendingCount = COUNT(*)
        FROM dbo.t_WorkFlowPending
        WHERE Source = @Source AND SourceID = @SourceID AND Stage = @StageID AND DeletedOn IS NULL;

        -- Count approvals
        SELECT @CurrentApprovedCount = COUNT(DISTINCT CreatedBy)
        FROM dbo.t_WorkFlowHistory 
        WHERE Source = @Source AND SourceID = @SourceID AND Stage = @StageID 
          AND StatusId = @StatusID
          AND DeletedOn IS NULL;

        -- Count eligible users (using effective tier permission)
        SELECT @TotalEligibleUsers = COUNT(DISTINCT u.Id)
        FROM t_Users u
        WHERE u.DeletedOn IS NULL
          AND EXISTS (SELECT 1 FROM dbo.f_getUserWithPermission(@EffectivePermissionId) WHERE Id = u.Id)
          AND u.Id <> ISNULL(@SubmitterId, 0);

        -- DETERMINE REQUIRED APPROVALS
        IF @WorkflowType = 'ALL'
            SET @TotalApprovalsRequired = @TotalEligibleUsers;
        ELSE IF @WorkflowType = 'MAJ'
            SET @TotalApprovalsRequired = (@TotalEligibleUsers / 2) + 1;
        ELSE IF @WorkflowType = 'CNT' OR @WorkflowType = 'AMT'
            SET @TotalApprovalsRequired = @ConfiguredCount;
        ELSE
            SET @TotalApprovalsRequired = @ConfiguredCount;

        -- Check if stage is complete
        IF @CurrentApprovedCount >= @TotalApprovalsRequired
        BEGIN
            SET @StageCompleted = 1;

            -- Delete remaining pending
            UPDATE dbo.t_WorkFlowPending
            SET DeletedBy = @SystemUserId, DeletedOn = GETDATE(), ModifiedBy = @SystemUserId, ModifiedOn = GETDATE()
            WHERE Source = @Source AND SourceID = @SourceID AND Stage = @StageID AND DeletedOn IS NULL;

            -- Mark history as approved
            UPDATE t_WorkFlowHistory
            SET isApproved = 1, ModifiedBy = @UserID, ModifiedOn = GETDATE()
            WHERE Source = @Source AND SourceID = @SourceID AND Stage = @StageID
              AND StatusId = @StatusID
              AND DeletedOn IS NULL;
        END
    END

    -- Check for pending in ANY stage
    SELECT @HasPendingApprovals = CASE 
        WHEN EXISTS (
            SELECT 1 FROM dbo.t_WorkFlowPending
            WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL
        ) THEN 1 ELSE 0 END;

    -- Send email
    SELECT @UserEmail = Email FROM t_Users WHERE Id = @UserID;
    IF @UserEmail IS NOT NULL AND @IsApprovalAction = 1
    BEGIN
        SET @EmailSubject = 'Workflow Action Confirmation - ' + @Description;
        SET @EmailMessage = 'Your workflow action for Source: ' + @Source + ', ID: ' + @SourceID + 
                           ' has been recorded as: ' + @Description + '.';
        
        IF @StageCompleted = 1
            SET @EmailMessage = @EmailMessage + ' The current stage has been completed.';
        ELSE
            SET @EmailMessage = @EmailMessage + ' The current stage requires more approvals.';

        EXEC p_sendNotificationEmail
             @UserID = @UserID,
             @Subject = @EmailSubject,
             @Message = @EmailMessage,
             @SenderId = @UserID,
             @Source = @Source,
             @SourceID = @SourceID;
    END

    -- Return results
    SELECT 
        'SUCCESS' AS Status,
        CASE 
            WHEN @HasPendingApprovals = 1 AND @StageCompleted = 0 THEN 'Approval recorded, awaiting other approvers'
            WHEN @HasPendingApprovals = 1 AND @StageCompleted = 1 THEN 'Stage completed, ready for next stage'
            WHEN @HasPendingApprovals = 0 AND @StageCompleted = 1 THEN 'Workflow completed'
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
