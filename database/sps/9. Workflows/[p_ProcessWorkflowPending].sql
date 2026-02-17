CREATE OR ALTER PROCEDURE [dbo].[p_ProcessWorkflowPending]
    @Source NVARCHAR(255) = NULL,
    @SourceID NVARCHAR(100) = NULL,
    @StageID BIGINT = NULL,
    @Amount DECIMAL(20,4) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE
        @Now DATETIME = GETDATE(),
        @SystemUserId BIGINT,
        @SubmittedStatusId BIGINT,
        @ApprovedStatusId BIGINT,
        @RejectedStatusId BIGINT,
        @ProcessedCount INT = 0,
        @ErrorCount INT = 0;

    -- =====================================================================
    -- 0. RESOLVE SYSTEM LOOKUPS
    -- =====================================================================
    SELECT TOP 1 @SystemUserId = Id
    FROM t_Users WITH (NOLOCK)
    WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

    IF @SystemUserId IS NULL
    BEGIN
        RAISERROR('System user (ERPSYS) not found.', 16, 1);
        RETURN;
    END

    SELECT @SubmittedStatusId = Id FROM t_CodeDetails WITH (NOLOCK) WHERE [Description] = 'Submitted for Approval';
    SELECT @ApprovedStatusId  = Id FROM t_CodeDetails WITH (NOLOCK) WHERE [Description] = 'Approved';
    SELECT @RejectedStatusId  = Id FROM t_CodeDetails WITH (NOLOCK) WHERE [Description] = 'Rejected';

    IF @SubmittedStatusId IS NULL OR @ApprovedStatusId IS NULL OR @RejectedStatusId IS NULL
    BEGIN
        RAISERROR('Required workflow status codes not found in t_CodeDetails (Submitted for Approval, Approved, Rejected).', 16, 1);
        RETURN;
    END

    -- =====================================================================
    -- MODE A: PROCESS SPECIFIC RECORD
    -- =====================================================================
    IF @Source IS NOT NULL AND @SourceID IS NOT NULL AND @StageID IS NOT NULL
    BEGIN
        DECLARE
            @PermissionId BIGINT,
            @WorkflowType NVARCHAR(50),
            @CountRequired INT,
            @MakerId BIGINT,
            @WorkFlowID BIGINT,
            @StageName NVARCHAR(255),
            @EffectivePermissionId BIGINT,
            @LimitType NVARCHAR(20) = 'DEFAULT',
            @TierMaxAmount DECIMAL(20,4) = NULL,
            @EligibleCount INT = 0;

        -- =================================================================
        -- A1. GET STAGE DETAILS
        -- =================================================================
        SELECT
            @PermissionId = s.PermissionId,
            @WorkflowType = wt.TypeId,
            @CountRequired = ISNULL(s.[Count], 1),
            @WorkFlowID = s.WorkFlowId,
            @StageName = s.StageName
        FROM t_WorkFlowStages s WITH (NOLOCK)
        LEFT JOIN t_WorkFlowTypes wt WITH (NOLOCK) ON s.WorkFlowTypeId = wt.Id
        WHERE s.Id = @StageID AND s.DeletedOn IS NULL;

        IF @PermissionId IS NULL
        BEGIN
            DECLARE @StageErrMsg NVARCHAR(200) = 'Stage not found or has no permission for StageID: ' + CAST(@StageID AS NVARCHAR(50));
            RAISERROR(@StageErrMsg, 16, 1);
            RETURN;
        END

        -- =================================================================
        -- A2. GET SUBMITTER FOR MAKER-CHECKER
        -- =================================================================
        SELECT TOP 1 @MakerId = CreatedBy
        FROM t_WorkFlowHistory WITH (NOLOCK)
        WHERE Source = @Source
          AND SourceID = @SourceID
          AND DeletedOn IS NULL
        ORDER BY CreatedOn ASC;

        -- =================================================================
        -- A3. AMOUNT-BASED TIER SELECTION
        -- =================================================================
        SET @EffectivePermissionId = @PermissionId;

        IF ISNULL(@WorkflowType, '') = 'AMT' AND @Amount IS NOT NULL AND @Amount > 0
        BEGIN
            IF EXISTS (
                SELECT 1 FROM t_WorkFlowLimits WITH (NOLOCK)
                WHERE WorkFlowStageId = @StageID AND DeletedOn IS NULL
            )
            BEGIN
                SELECT TOP 1
                    @EffectivePermissionId = PermissionId,
                    @TierMaxAmount = MaxAmount,
                    @LimitType = LimitType
                FROM dbo.f_getPermissionForAmount(@StageID, @Amount);

                -- Fallback to stage default if function returns nothing
                IF @EffectivePermissionId IS NULL
                    SET @EffectivePermissionId = @PermissionId;
            END
        END

        -- =================================================================
        -- A4. GET ELIGIBLE USERS
        -- =================================================================
        DECLARE @EligibleUsers TABLE (
            Id BIGINT PRIMARY KEY,
            Name NVARCHAR(255),
            Email NVARCHAR(255)
        );

        INSERT INTO @EligibleUsers (Id, Name, Email)
        SELECT DISTINCT u.Id, u.Name, u.Email
        FROM t_Users u WITH (NOLOCK)
        WHERE u.DeletedOn IS NULL
          AND u.Id <> ISNULL(@MakerId, 0)
          AND u.UserID <> 'CSADM'
          AND EXISTS (
              SELECT 1 FROM dbo.f_getUserWithPermission(@EffectivePermissionId) perm
              WHERE perm.Id = u.Id
          )
          AND NOT EXISTS (
              SELECT 1 FROM dbo.t_WorkFlowHistory h WITH (NOLOCK)
              WHERE h.Source = @Source
                AND h.SourceID = @SourceID
                AND h.Stage = CAST(@StageID AS NVARCHAR(200))
                AND h.CreatedBy = u.Id
                AND h.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
                AND h.DeletedOn IS NULL
          );

        SET @EligibleCount = (SELECT COUNT(*) FROM @EligibleUsers);

        IF @EligibleCount = 0
        BEGIN
            RAISERROR('No eligible approvers found for this stage.', 16, 1);
            RETURN;
        END

        -- =================================================================
        -- A5. CREATE PENDING APPROVALS (transactional)
        -- =================================================================
        BEGIN TRY
            BEGIN TRANSACTION;

            -- Soft-delete existing pending for this source+stage (preserve audit trail)
            UPDATE dbo.t_WorkFlowPending
            SET DeletedBy = @SystemUserId,
                DeletedOn = @Now,
                ModifiedBy = @SystemUserId,
                ModifiedOn = @Now
            WHERE Source = @Source
              AND SourceID = @SourceID
              AND Stage = CAST(@StageID AS NVARCHAR(200))
              AND DeletedOn IS NULL;

            -- Insert pending for each eligible user
            INSERT INTO t_WorkFlowPending (
                Source, SourceID, Stage, UserId,
                CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
            )
            SELECT
                @Source,
                @SourceID,
                CAST(@StageID AS NVARCHAR(200)),
                e.Id,
                @SystemUserId,
                @Now,
                @SystemUserId,
                @Now
            FROM @EligibleUsers e;

            SET @ProcessedCount = @@ROWCOUNT;

            -- Mark history as processed (isApproved = 0 means "pending action created")
            UPDATE t_WorkFlowHistory
            SET isApproved = 0,
                ModifiedBy = @SystemUserId,
                ModifiedOn = @Now
            WHERE Source = @Source
              AND SourceID = @SourceID
              AND Stage = CAST(@StageID AS NVARCHAR(200))
              AND StatusId = @SubmittedStatusId
              AND DeletedOn IS NULL
              AND isApproved IS NULL;

            COMMIT TRANSACTION;
        END TRY
        BEGIN CATCH
            IF @@TRANCOUNT > 0
                ROLLBACK TRANSACTION;

            DECLARE @SpecificError NVARCHAR(4000) = ERROR_MESSAGE();
            RAISERROR(@SpecificError, 16, 1);
            RETURN;
        END CATCH

        -- =================================================================
        -- A6. SEND NOTIFICATIONS (outside transaction)
        -- =================================================================
        DECLARE
            @NotifyUserId BIGINT,
            @NotifyEmail NVARCHAR(255),
            @NotifyName NVARCHAR(255),
            @EmailSubject NVARCHAR(255),
            @EmailMessage NVARCHAR(MAX);

        DECLARE notify_cursor CURSOR LOCAL FAST_FORWARD FOR
            SELECT Id, Email, Name FROM @EligibleUsers;

        OPEN notify_cursor;
        FETCH NEXT FROM notify_cursor INTO @NotifyUserId, @NotifyEmail, @NotifyName;

        WHILE @@FETCH_STATUS = 0
        BEGIN
            BEGIN TRY
                IF @NotifyEmail IS NOT NULL AND LEN(@NotifyEmail) > 5
                BEGIN
                    SET @EmailSubject = 'Workflow Approval Required - ' + ISNULL(@StageName, 'Stage ' + CAST(@StageID AS NVARCHAR(50)));
                    SET @EmailMessage =
                        'Dear ' + ISNULL(@NotifyName, 'User') + ',' + CHAR(13) + CHAR(10) +
                        'A new item requires your approval.' + CHAR(13) + CHAR(10) +
                        'Source: ' + ISNULL(@Source, '[Unknown]') + CHAR(13) + CHAR(10) +
                        'Record ID: ' + ISNULL(@SourceID, '[Unknown]') + CHAR(13) + CHAR(10) +
                        'Stage: ' + ISNULL(@StageName, '[Unknown]') + CHAR(13) + CHAR(10) +
                        'Please log in to review and take action.';

                    EXEC p_sendNotificationEmail
                        @UserID = @NotifyUserId,
                        @Subject = @EmailSubject,
                        @Message = @EmailMessage,
                        @SenderId = @SystemUserId,
                        @Source = @Source,
                        @SourceID = @SourceID;
                END
            END TRY
            BEGIN CATCH
                -- Email failure should not fail the workflow
                SET @ErrorCount += 1;
            END CATCH

            FETCH NEXT FROM notify_cursor INTO @NotifyUserId, @NotifyEmail, @NotifyName;
        END

        CLOSE notify_cursor;
        DEALLOCATE notify_cursor;

        -- =================================================================
        -- A7. RETURN RESULT
        -- =================================================================
        SELECT
            'SUCCESS' AS Status,
            @ProcessedCount AS InsertedPendingCount,
            @CountRequired AS ApprovalsRequired,
            @EffectivePermissionId AS EffectivePermissionId,
            @EligibleCount AS EligibleUserCount,
            @ErrorCount AS NotificationErrors;
        RETURN;
    END

    -- =====================================================================
    -- MODE B: BATCH PROCESSING (no specific record — process all unassigned)
    -- =====================================================================
    BEGIN TRY

        IF OBJECT_ID('tempdb..#WorkSet') IS NOT NULL DROP TABLE #WorkSet;
        CREATE TABLE #WorkSet (
            RowNum INT IDENTITY(1,1) PRIMARY KEY,
            HistoryId BIGINT,
            Source NVARCHAR(255),
            SourceID NVARCHAR(100),
            StageId BIGINT,
            PermissionId BIGINT,
            WorkflowType NVARCHAR(20),
            CountRequired INT,
            MakerId BIGINT,
            StageName NVARCHAR(255)
        );

        -- Get latest unprocessed history entry per Source+SourceID (deduplicated)
        ;WITH LatestPending AS (
            SELECT
                h.Id, h.Source, h.SourceID, h.Stage, h.CreatedBy,
                ROW_NUMBER() OVER (PARTITION BY h.Source, h.SourceID ORDER BY h.CreatedOn DESC) AS rn
            FROM t_WorkFlowHistory h WITH (NOLOCK)
            WHERE h.DeletedOn IS NULL
              AND h.isApproved IS NULL
              AND h.StatusId = @SubmittedStatusId
        )
        INSERT INTO #WorkSet (HistoryId, Source, SourceID, StageId, PermissionId, WorkflowType, CountRequired, MakerId, StageName)
        SELECT
            lp.Id, lp.Source, lp.SourceID, CAST(lp.Stage AS BIGINT),
            s.PermissionId, wt.TypeId, ISNULL(s.[Count], 1), lp.CreatedBy, s.StageName
        FROM LatestPending lp
        JOIN t_WorkFlowStages s WITH (NOLOCK) ON s.Id = CAST(lp.Stage AS BIGINT) AND s.DeletedOn IS NULL
        LEFT JOIN t_WorkFlowTypes wt WITH (NOLOCK) ON s.WorkFlowTypeId = wt.Id
        WHERE lp.rn = 1
          AND NOT EXISTS (
              SELECT 1 FROM t_WorkFlowPending p WITH (NOLOCK)
              WHERE p.Source = lp.Source
                AND p.SourceID = lp.SourceID
                AND p.Stage = lp.Stage
                AND p.DeletedOn IS NULL
          );

        DECLARE @BatchTotal INT = (SELECT COUNT(*) FROM #WorkSet);

        IF @BatchTotal = 0
        BEGIN
            SELECT 'SUCCESS' AS Status, 0 AS InsertedPendingCount, 0 AS Errors, 'No pending items to process' AS Message;
            DROP TABLE IF EXISTS #WorkSet;
            RETURN;
        END

        -- Process each work item
        DECLARE
            @BatchRowNum INT = 1,
            @BatchMaxRow INT = @BatchTotal,
            @BatchHistoryId BIGINT,
            @BatchSource NVARCHAR(255),
            @BatchSourceID NVARCHAR(100),
            @BatchStageId BIGINT,
            @BatchPermId BIGINT,
            @BatchWFType NVARCHAR(20),
            @BatchCountReq INT,
            @BatchMaker BIGINT,
            @BatchStageName NVARCHAR(255),
            @BatchEffPerm BIGINT,
            @BatchEligCount INT;

        WHILE @BatchRowNum <= @BatchMaxRow
        BEGIN
            SELECT
                @BatchHistoryId = HistoryId,
                @BatchSource = Source,
                @BatchSourceID = SourceID,
                @BatchStageId = StageId,
                @BatchPermId = PermissionId,
                @BatchWFType = WorkflowType,
                @BatchCountReq = CountRequired,
                @BatchMaker = MakerId,
                @BatchStageName = StageName
            FROM #WorkSet
            WHERE RowNum = @BatchRowNum;

            BEGIN TRY
                -- Validate permission exists
                IF @BatchPermId IS NULL
                BEGIN
                    SET @ErrorCount += 1;
                    SET @BatchRowNum += 1;
                    CONTINUE;
                END

                SET @BatchEffPerm = @BatchPermId;

                -- For AMT types in batch, we can't determine amount without dynamic SQL
                -- so we use the default stage permission. For amount-aware processing,
                -- callers should use Mode A (specific record) with @Amount parameter.

                -- Get eligible users for this batch item
                DECLARE @BatchEligible TABLE (
                    Id BIGINT PRIMARY KEY,
                    Name NVARCHAR(255),
                    Email NVARCHAR(255)
                );
                DELETE FROM @BatchEligible;

                INSERT INTO @BatchEligible (Id, Name, Email)
                SELECT DISTINCT u.Id, u.Name, u.Email
                FROM t_Users u WITH (NOLOCK)
                WHERE u.DeletedOn IS NULL
                  AND u.Id <> ISNULL(@BatchMaker, 0)
                  AND u.UserID <> 'CSADM'
                  AND EXISTS (
                      SELECT 1 FROM dbo.f_getUserWithPermission(@BatchEffPerm) perm
                      WHERE perm.Id = u.Id
                  )
                  AND NOT EXISTS (
                      SELECT 1 FROM dbo.t_WorkFlowHistory h WITH (NOLOCK)
                      WHERE h.Source = @BatchSource
                        AND h.SourceID = @BatchSourceID
                        AND h.Stage = CAST(@BatchStageId AS NVARCHAR(200))
                        AND h.CreatedBy = u.Id
                        AND h.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
                        AND h.DeletedOn IS NULL
                  );

                SET @BatchEligCount = (SELECT COUNT(*) FROM @BatchEligible);

                IF @BatchEligCount = 0
                BEGIN
                    SET @ErrorCount += 1;
                    SET @BatchRowNum += 1;
                    CONTINUE;
                END

                -- Transactional write for this batch item
                BEGIN TRANSACTION;

                -- Soft-delete existing pending
                UPDATE dbo.t_WorkFlowPending
                SET DeletedBy = @SystemUserId,
                    DeletedOn = @Now,
                    ModifiedBy = @SystemUserId,
                    ModifiedOn = @Now
                WHERE Source = @BatchSource
                  AND SourceID = @BatchSourceID
                  AND Stage = CAST(@BatchStageId AS NVARCHAR(200))
                  AND DeletedOn IS NULL;

                -- Insert new pending records
                INSERT INTO t_WorkFlowPending (
                    Source, SourceID, Stage, UserId,
                    CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
                )
                SELECT
                    @BatchSource,
                    @BatchSourceID,
                    CAST(@BatchStageId AS NVARCHAR(200)),
                    e.Id,
                    @SystemUserId,
                    @Now,
                    @SystemUserId,
                    @Now
                FROM @BatchEligible e;

                SET @ProcessedCount += @@ROWCOUNT;

                -- Mark history as processed
                UPDATE t_WorkFlowHistory
                SET isApproved = 0,
                    ModifiedBy = @SystemUserId,
                    ModifiedOn = @Now
                WHERE Id = @BatchHistoryId
                  AND isApproved IS NULL;

                COMMIT TRANSACTION;

                -- Send notifications (outside transaction, per batch item)
                DECLARE
                    @BNotifyId BIGINT,
                    @BNotifyEmail NVARCHAR(255),
                    @BNotifyName NVARCHAR(255);

                DECLARE batch_notify_cursor CURSOR LOCAL FAST_FORWARD FOR
                    SELECT Id, Email, Name FROM @BatchEligible;

                OPEN batch_notify_cursor;
                FETCH NEXT FROM batch_notify_cursor INTO @BNotifyId, @BNotifyEmail, @BNotifyName;

                WHILE @@FETCH_STATUS = 0
                BEGIN
                    BEGIN TRY
                        IF @BNotifyEmail IS NOT NULL AND LEN(@BNotifyEmail) > 5
                        BEGIN
                            DECLARE @BSubject NVARCHAR(255) = 'Workflow Approval Required - ' + ISNULL(@BatchStageName, 'Stage ' + CAST(@BatchStageId AS NVARCHAR(50)));
                            DECLARE @BMessage NVARCHAR(MAX) =
                                'Dear ' + ISNULL(@BNotifyName, 'User') + ',' + CHAR(13) + CHAR(10) +
                                'A new item requires your approval.' + CHAR(13) + CHAR(10) +
                                'Source: ' + ISNULL(@BatchSource, '[Unknown]') + CHAR(13) + CHAR(10) +
                                'Record ID: ' + ISNULL(@BatchSourceID, '[Unknown]') + CHAR(13) + CHAR(10) +
                                'Stage: ' + ISNULL(@BatchStageName, '[Unknown]') + CHAR(13) + CHAR(10) +
                                'Please log in to review and take action.';

                            EXEC p_sendNotificationEmail
                                @UserID = @BNotifyId,
                                @Subject = @BSubject,
                                @Message = @BMessage,
                                @SenderId = @SystemUserId,
                                @Source = @BatchSource,
                                @SourceID = @BatchSourceID;
                        END
                    END TRY
                    BEGIN CATCH
                        -- Email failure should not fail the batch
                    END CATCH

                    FETCH NEXT FROM batch_notify_cursor INTO @BNotifyId, @BNotifyEmail, @BNotifyName;
                END

                CLOSE batch_notify_cursor;
                DEALLOCATE batch_notify_cursor;

            END TRY
            BEGIN CATCH
                IF @@TRANCOUNT > 0
                    ROLLBACK TRANSACTION;

                SET @ErrorCount += 1;
            END CATCH

            SET @BatchRowNum += 1;
        END

        DROP TABLE IF EXISTS #WorkSet;

        SELECT
            'SUCCESS' AS Status,
            @ProcessedCount AS InsertedPendingCount,
            @BatchTotal AS TotalItemsFound,
            @ErrorCount AS Errors;

    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        DROP TABLE IF EXISTS #WorkSet;

        DECLARE @BatchError NVARCHAR(4000) = ERROR_MESSAGE();
        RAISERROR(@BatchError, 16, 1);
    END CATCH
END
GO