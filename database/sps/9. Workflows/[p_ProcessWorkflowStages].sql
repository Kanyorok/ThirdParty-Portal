CREATE OR ALTER PROCEDURE [dbo].[p_ProcessWorkflowStages]
    @PermissionId BIGINT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE
        @SystemUserId BIGINT,
        @Now DATETIME = GETDATE(),
        @CurrentOrder INT,
        @StageId BIGINT,
        @WorkFlowID BIGINT,
        @Count INT,
        @WorkflowType NVARCHAR(100),
        @Source NVARCHAR(255),
        @SourceId NVARCHAR(100),
        @ApprovedStatusId BIGINT,
        @ActualApprovals INT,
        @RequiredApprovals INT,
        @NextStageId BIGINT,
        @NextStagePermission BIGINT,
        @NextWorkflowType NVARCHAR(100),
        @NextCount INT,
        @NextStageName NVARCHAR(255),
        @WorkflowStagePermission BIGINT,
        @MakerId BIGINT,
        @EligibleCount INT,
        -- Email
        @NotifyUserId BIGINT,
        @NotifyEmail NVARCHAR(255),
        @NotifyName NVARCHAR(255),
        @EmailSubject NVARCHAR(255),
        @EmailMessage NVARCHAR(MAX),
        -- Counters
        @ProcessedCount INT = 0,
        @AdvancedCount INT = 0,
        @CompletedCount INT = 0,
        @ErrorCount INT = 0,
        @SkippedCount INT = 0,
        @OrphanCount INT = 0,
        @CleanupCount INT = 0;

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

    SELECT TOP 1 @ApprovedStatusId = ID
    FROM t_CodeDetails WITH (NOLOCK)
    WHERE [Description] IN ('Approved', 'Approval')
    ORDER BY ID;

    IF @ApprovedStatusId IS NULL
    BEGIN
        RAISERROR('Approved status not found in t_CodeDetails.', 16, 1);
        RETURN;
    END

    BEGIN TRY

        -- =================================================================
        -- PHASE 1: PROCESS ACTIVE PENDING ITEMS
        -- =================================================================
        IF OBJECT_ID('tempdb..#ApprovalItems') IS NOT NULL
            DROP TABLE #ApprovalItems;

        ;WITH LatestApproved AS (
            SELECT
                p.Source,
                p.SourceID,
                CAST(p.Stage AS BIGINT) AS StageId,
                s.PermissionId,
                s.WorkFlowId,
                ROW_NUMBER() OVER (
                    PARTITION BY p.Source, p.SourceID
                    ORDER BY CAST(p.Stage AS BIGINT) DESC
                ) AS rn
            FROM t_WorkFlowPending p WITH (NOLOCK)
            INNER JOIN t_WorkFlowHistory h WITH (NOLOCK)
                ON p.Source = h.Source
                AND p.SourceID = h.SourceID
                AND p.Stage = h.Stage
                AND h.StatusId = @ApprovedStatusId
                AND h.DeletedOn IS NULL
            INNER JOIN t_WorkFlowStages s WITH (NOLOCK)
                ON s.Id = CAST(p.Stage AS BIGINT)
                AND s.DeletedOn IS NULL
            WHERE p.DeletedOn IS NULL
        )
        SELECT
            ROW_NUMBER() OVER (ORDER BY Source, SourceID) AS RowNum,
            Source, SourceID, StageId, PermissionId, WorkFlowId
        INTO #ApprovalItems
        FROM LatestApproved
        WHERE rn = 1;

        DECLARE
            @RowNum INT = 1,
            @MaxRow INT = ISNULL((SELECT MAX(RowNum) FROM #ApprovalItems), 0),
            @ItemWorkFlowId BIGINT;

        WHILE @RowNum <= @MaxRow
        BEGIN
            -- Reset per-iteration variables
            SET @NextStageId = NULL;
            SET @MakerId = NULL;
            SET @ActualApprovals = 0;
            SET @RequiredApprovals = 0;
            SET @CurrentOrder = NULL;

            SELECT
                @Source = Source,
                @SourceId = SourceID,
                @StageId = StageId,
                @WorkflowStagePermission = PermissionId,
                @ItemWorkFlowId = WorkFlowId
            FROM #ApprovalItems
            WHERE RowNum = @RowNum;

            SET @ProcessedCount += 1;

            BEGIN TRY

                -- Skip if permission filter is set and doesn't match
                IF @PermissionId IS NOT NULL AND @PermissionId <> @WorkflowStagePermission
                BEGIN
                    SET @SkippedCount += 1;
                    SET @RowNum += 1;
                    CONTINUE;
                END

                -- Skip if NULL permission
                IF @WorkflowStagePermission IS NULL
                BEGIN
                    SET @SkippedCount += 1;
                    SET @RowNum += 1;
                    CONTINUE;
                END

                -- Get current stage metadata (single query)
                SELECT
                    @CurrentOrder = s.[Order],
                    @WorkFlowID = s.WorkFlowId,
                    @WorkflowStagePermission = s.PermissionId,
                    @WorkflowType = ISNULL(wt.TypeID, 'CNT'),
                    @Count = ISNULL(s.[Count], 1)
                FROM t_WorkFlowStages s WITH (NOLOCK)
                LEFT JOIN t_WorkFlowTypes wt WITH (NOLOCK) ON s.WorkFlowTypeId = wt.Id
                WHERE s.Id = @StageId
                  AND s.DeletedOn IS NULL;

                IF @CurrentOrder IS NULL
                BEGIN
                    SET @ErrorCount += 1;
                    SET @RowNum += 1;
                    CONTINUE;
                END

                -- Count actual approvals for this stage
                SELECT @ActualApprovals = COUNT(DISTINCT h.CreatedBy)
                FROM t_WorkFlowHistory h WITH (NOLOCK)
                WHERE h.Source = @Source
                  AND h.SourceID = @SourceId
                  AND h.Stage = CAST(@StageId AS NVARCHAR(200))
                  AND h.StatusId = @ApprovedStatusId
                  AND h.DeletedOn IS NULL;

                -- Determine required approvals by workflow type
                IF @WorkflowType = 'ALL'
                BEGIN
                    SELECT @RequiredApprovals = COUNT(*)
                    FROM dbo.f_getUserWithPermission(@WorkflowStagePermission);
                END
                ELSE IF @WorkflowType = 'MAJ'
                BEGIN
                    DECLARE @MajTotal INT;
                    SELECT @MajTotal = COUNT(*)
                    FROM dbo.f_getUserWithPermission(@WorkflowStagePermission);

                    SET @RequiredApprovals = CEILING(@MajTotal * 1.0 / 2);
                END
                ELSE
                BEGIN
                    SET @RequiredApprovals = @Count;
                END

                IF @RequiredApprovals < 1
                    SET @RequiredApprovals = 1;

                -- Not enough approvals yet — skip
                IF @ActualApprovals < @RequiredApprovals
                BEGIN
                    SET @SkippedCount += 1;
                    SET @RowNum += 1;
                    CONTINUE;
                END

                -- Find next stage (scoped to same workflow)
                SELECT TOP 1 @NextStageId = s2.Id
                FROM t_WorkFlowStages s2 WITH (NOLOCK)
                WHERE s2.[Order] = @CurrentOrder + 1
                  AND s2.WorkFlowId = @WorkFlowID
                  AND s2.DeletedOn IS NULL
                ORDER BY s2.Id;

                IF @NextStageId IS NULL
                BEGIN
                    -- WORKFLOW COMPLETE — no next stage
                    BEGIN TRANSACTION;

                    UPDATE dbo.t_WorkFlowPending
                    SET DeletedBy = @SystemUserId,
                        DeletedOn = @Now,
                        ModifiedBy = @SystemUserId,
                        ModifiedOn = @Now
                    WHERE Source = @Source
                      AND SourceID = @SourceId
                      AND Stage = CAST(@StageId AS NVARCHAR(200))
                      AND DeletedOn IS NULL;

                    -- Update source table to final approved status
                    -- Only update if not already set (idempotent)
                    BEGIN TRY
                        DECLARE @FinalTableName NVARCHAR(255) = PARSENAME(@Source, 1);
                        DECLARE @FinalKeyColumn NVARCHAR(128) = 'Id';
                        DECLARE @FinalUpdateSQL NVARCHAR(MAX);

                        -- Determine primary key column
                        SELECT TOP 1 @FinalKeyColumn = COLUMN_NAME
                        FROM INFORMATION_SCHEMA.COLUMNS WITH (NOLOCK)
                        WHERE TABLE_NAME = @FinalTableName
                          AND COLUMN_NAME IN ('Id', 'ID', 'PlanID')
                        ORDER BY CASE COLUMN_NAME WHEN 'Id' THEN 1 WHEN 'ID' THEN 2 WHEN 'PlanID' THEN 3 END;

                        IF @FinalKeyColumn IS NULL
                            SET @FinalKeyColumn = 'Id';

                        -- Check that table and Status column exist
                        IF EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = @FinalTableName)
                           AND EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = @FinalTableName AND COLUMN_NAME = 'Status')
                           AND EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = @FinalTableName AND COLUMN_NAME = 'ModifiedBy')
                           AND EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = @FinalTableName AND COLUMN_NAME = 'ModifiedOn')
                        BEGIN
                            SET @FinalUpdateSQL = N'UPDATE ' + QUOTENAME(@FinalTableName) +
                                N' SET Status = @StatusVal, ModifiedBy = @UID, ModifiedOn = GETDATE()' +
                                N' WHERE ' + QUOTENAME(@FinalKeyColumn) + N' = @SID';

                            EXEC sp_executesql @FinalUpdateSQL,
                                N'@StatusVal NVARCHAR(50), @SID NVARCHAR(100), @UID BIGINT',
                                CAST(@ApprovedStatusId AS NVARCHAR(50)), @SourceId, @SystemUserId;
                        END
                    END TRY
                    BEGIN CATCH
                        -- Source table update failure should not break the workflow completion
                        -- PHP advanceToNextStage() will also attempt this update as a safety net
                    END CATCH

                    COMMIT TRANSACTION;

                    SET @CompletedCount += 1;
                    SET @RowNum += 1;
                    CONTINUE;
                END

                -- Get next stage metadata
                SELECT TOP 1
                    @NextStagePermission = s2.PermissionId,
                    @NextWorkflowType = ISNULL(wt2.TypeID, 'CNT'),
                    @NextCount = ISNULL(s2.[Count], 1),
                    @NextStageName = s2.StageName
                FROM t_WorkFlowStages s2 WITH (NOLOCK)
                LEFT JOIN t_WorkFlowTypes wt2 WITH (NOLOCK) ON s2.WorkFlowTypeId = wt2.Id
                WHERE s2.Id = @NextStageId
                  AND s2.DeletedOn IS NULL;

                IF @NextStagePermission IS NULL
                BEGIN
                    SET @ErrorCount += 1;
                    SET @RowNum += 1;
                    CONTINUE;
                END

                -- Check if next stage is already complete — skip if so
                DECLARE @NextStageActualApprovals INT = 0;
                SELECT @NextStageActualApprovals = COUNT(DISTINCT h.CreatedBy)
                FROM t_WorkFlowHistory h WITH (NOLOCK)
                WHERE h.Source = @Source
                  AND h.SourceID = @SourceId
                  AND h.Stage = CAST(@NextStageId AS NVARCHAR(200))
                  AND h.StatusId = @ApprovedStatusId
                  AND h.DeletedOn IS NULL;

                IF @NextStageActualApprovals >= @NextCount
                BEGIN
                    -- Next stage already complete — just clean up current stage
                    BEGIN TRANSACTION;

                    UPDATE dbo.t_WorkFlowPending
                    SET DeletedBy = @SystemUserId,
                        DeletedOn = @Now,
                        ModifiedBy = @SystemUserId,
                        ModifiedOn = @Now
                    WHERE Source = @Source
                      AND SourceID = @SourceId
                      AND Stage = CAST(@StageId AS NVARCHAR(200))
                      AND DeletedOn IS NULL;

                    COMMIT TRANSACTION;

                    SET @SkippedCount += 1;
                    SET @RowNum += 1;
                    CONTINUE;
                END

                -- Get maker for maker-checker exclusion
                SELECT TOP 1 @MakerId = h.CreatedBy
                FROM t_WorkFlowHistory h WITH (NOLOCK)
                WHERE h.Source = @Source
                  AND h.SourceID = @SourceId
                  AND h.DeletedOn IS NULL
                ORDER BY h.CreatedOn ASC;

                -- Get eligible users for next stage
                DECLARE @NextStageUsers TABLE (
                    Id BIGINT PRIMARY KEY,
                    Name NVARCHAR(255),
                    Email NVARCHAR(255)
                );
                DELETE FROM @NextStageUsers;

                INSERT INTO @NextStageUsers (Id, Name, Email)
                SELECT DISTINCT u.Id, u.Name, u.Email
                FROM t_Users u WITH (NOLOCK)
                WHERE u.DeletedOn IS NULL
                  AND u.Id <> ISNULL(@MakerId, 0)
                  AND u.UserID <> 'CSADM'
                  AND u.Id <> @SystemUserId
                  AND EXISTS (
                      SELECT 1 FROM dbo.f_getUserWithPermission(@NextStagePermission) perm
                      WHERE perm.Id = u.Id
                  )
                  AND NOT EXISTS (
                      SELECT 1 FROM t_WorkFlowHistory h WITH (NOLOCK)
                      WHERE h.Source = @Source
                        AND h.SourceID = @SourceId
                        AND h.Stage = CAST(@NextStageId AS NVARCHAR(200))
                        AND h.CreatedBy = u.Id
                        AND h.StatusId = @ApprovedStatusId
                        AND h.DeletedOn IS NULL
                  )
                  AND NOT EXISTS (
                      SELECT 1 FROM t_WorkFlowPending p WITH (NOLOCK)
                      WHERE p.Source = @Source
                        AND p.SourceID = @SourceId
                        AND p.Stage = CAST(@NextStageId AS NVARCHAR(200))
                        AND p.UserId = u.Id
                        AND p.DeletedOn IS NULL
                  );

                SET @EligibleCount = (SELECT COUNT(*) FROM @NextStageUsers);

                IF @EligibleCount = 0
                BEGIN
                    SET @ErrorCount += 1;
                    SET @RowNum += 1;
                    CONTINUE;
                END

                -- TRANSACTIONAL: Soft-delete current stage + insert next stage
                BEGIN TRANSACTION;

                UPDATE dbo.t_WorkFlowPending
                SET DeletedBy = @SystemUserId,
                    DeletedOn = @Now,
                    ModifiedBy = @SystemUserId,
                    ModifiedOn = @Now
                WHERE Source = @Source
                  AND SourceID = @SourceId
                  AND Stage = CAST(@StageId AS NVARCHAR(200))
                  AND DeletedOn IS NULL;

                INSERT INTO t_WorkFlowPending (
                    Source, SourceID, Stage, UserId,
                    CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
                )
                SELECT
                    @Source,
                    @SourceId,
                    CAST(@NextStageId AS NVARCHAR(200)),
                    u.Id,
                    @SystemUserId,
                    @Now,
                    @SystemUserId,
                    @Now
                FROM @NextStageUsers u;

                COMMIT TRANSACTION;

                SET @AdvancedCount += 1;

                -- Email notifications (outside transaction)
                DECLARE next_notify_cursor CURSOR LOCAL FAST_FORWARD FOR
                    SELECT Id, Email, Name FROM @NextStageUsers;

                OPEN next_notify_cursor;
                FETCH NEXT FROM next_notify_cursor INTO @NotifyUserId, @NotifyEmail, @NotifyName;

                WHILE @@FETCH_STATUS = 0
                BEGIN
                    BEGIN TRY
                        IF @NotifyEmail IS NOT NULL AND LEN(@NotifyEmail) > 5
                        BEGIN
                            SET @EmailSubject = 'Workflow Approval Required - ' +
                                ISNULL(@NextStageName, 'Stage ' + CAST(@NextStageId AS NVARCHAR(50)));
                            SET @EmailMessage =
                                'Dear ' + ISNULL(@NotifyName, 'User') + ',' + CHAR(13) + CHAR(10) +
                                'A workflow item has advanced to your stage and requires your approval.' + CHAR(13) + CHAR(10) +
                                'Source: ' + ISNULL(@Source, '[Unknown]') + CHAR(13) + CHAR(10) +
                                'Record ID: ' + ISNULL(@SourceId, '[Unknown]') + CHAR(13) + CHAR(10) +
                                'Stage: ' + ISNULL(@NextStageName, '[Unknown]') + CHAR(13) + CHAR(10) +
                                'Please log in to review and take action.';

                            EXEC p_sendNotificationEmail
                                @UserID = @NotifyUserId,
                                @Subject = @EmailSubject,
                                @Message = @EmailMessage,
                                @SenderId = @SystemUserId,
                                @Source = @Source,
                                @SourceID = @SourceId;
                        END
                    END TRY
                    BEGIN CATCH
                        -- Email failure should not break the workflow
                    END CATCH

                    FETCH NEXT FROM next_notify_cursor INTO @NotifyUserId, @NotifyEmail, @NotifyName;
                END

                CLOSE next_notify_cursor;
                DEALLOCATE next_notify_cursor;

            END TRY
            BEGIN CATCH
                IF @@TRANCOUNT > 0
                    ROLLBACK TRANSACTION;

                SET @ErrorCount += 1;
            END CATCH

            SET @RowNum += 1;
        END

        DROP TABLE #ApprovalItems;

        -- =================================================================
        -- PHASE 2: ORPHAN RECOVERY
        -- Detect items where history shows a stage is complete
        -- but no pending records exist for the next stage.
        -- This handles edge cases where Phase 1 was never called
        -- or failed mid-way.
        -- =================================================================
        IF OBJECT_ID('tempdb..#OrphanItems') IS NOT NULL
            DROP TABLE #OrphanItems;

        ;WITH CompletedStages AS (
            SELECT
                h.Source,
                h.SourceID,
                CAST(h.Stage AS BIGINT) AS StageId,
                s.[Order] AS StageOrder,
                s.WorkFlowId,
                COUNT(DISTINCT h.CreatedBy) AS ApprovalCount,
                ISNULL(s.[Count], 1) AS RequiredCount
            FROM t_WorkFlowHistory h WITH (NOLOCK)
            INNER JOIN t_WorkFlowStages s WITH (NOLOCK)
                ON CAST(h.Stage AS BIGINT) = s.Id
                AND s.DeletedOn IS NULL
            WHERE h.StatusId = @ApprovedStatusId
              AND h.DeletedOn IS NULL
            GROUP BY h.Source, h.SourceID, CAST(h.Stage AS BIGINT), s.[Order], s.WorkFlowId, ISNULL(s.[Count], 1)
            HAVING COUNT(DISTINCT h.CreatedBy) >= ISNULL(s.[Count], 1)
        ),
        LatestCompletedPerItem AS (
            SELECT *,
                ROW_NUMBER() OVER (PARTITION BY Source, SourceID ORDER BY StageOrder DESC) AS rn
            FROM CompletedStages
        )
        SELECT
            lc.Source, lc.SourceID, lc.StageId, lc.StageOrder, lc.WorkFlowId,
            ns.Id AS NextStageId,
            ns.PermissionId AS NextPermissionId,
            ISNULL(ns.[Count], 1) AS NextCount,
            ns.StageName AS NextStageName,
            ROW_NUMBER() OVER (ORDER BY lc.Source, lc.SourceID) AS RowNum
        INTO #OrphanItems
        FROM LatestCompletedPerItem lc
        CROSS APPLY (
            SELECT TOP 1 ns.Id, ns.PermissionId, ns.[Count], ns.StageName
            FROM t_WorkFlowStages ns WITH (NOLOCK)
            WHERE ns.[Order] = lc.StageOrder + 1
              AND ns.WorkFlowId = lc.WorkFlowId
              AND ns.DeletedOn IS NULL
            ORDER BY ns.Id
        ) ns
        WHERE lc.rn = 1
          -- No active pending for this item at all
          AND NOT EXISTS (
              SELECT 1 FROM t_WorkFlowPending p WITH (NOLOCK)
              WHERE p.Source = lc.Source
                AND p.SourceID = lc.SourceID
                AND p.DeletedOn IS NULL
          )
          -- Next stage is NOT already complete
          AND NOT EXISTS (
              SELECT 1 FROM t_WorkFlowHistory h2 WITH (NOLOCK)
              INNER JOIN t_WorkFlowStages s2 WITH (NOLOCK) ON CAST(h2.Stage AS BIGINT) = s2.Id
              WHERE h2.Source = lc.Source
                AND h2.SourceID = lc.SourceID
                AND s2.Id = ns.Id
                AND h2.StatusId = @ApprovedStatusId
                AND h2.DeletedOn IS NULL
              GROUP BY h2.Source, h2.SourceID
              HAVING COUNT(DISTINCT h2.CreatedBy) >= ISNULL(ns.[Count], 1)
          );

        DECLARE
            @OrphanRow INT = 1,
            @OrphanMax INT = ISNULL((SELECT MAX(RowNum) FROM #OrphanItems), 0),
            @OrphanSource NVARCHAR(255),
            @OrphanSourceId NVARCHAR(100),
            @OrphanNextStageId BIGINT,
            @OrphanNextPermId BIGINT,
            @OrphanNextCount INT,
            @OrphanNextStageName NVARCHAR(255),
            @OrphanWorkFlowId BIGINT,
            @OrphanStageOrder INT,
            @OrphanMakerId BIGINT;

        WHILE @OrphanRow <= @OrphanMax
        BEGIN
            SELECT
                @OrphanSource = Source,
                @OrphanSourceId = SourceID,
                @OrphanNextStageId = NextStageId,
                @OrphanNextPermId = NextPermissionId,
                @OrphanNextCount = NextCount,
                @OrphanNextStageName = NextStageName,
                @OrphanWorkFlowId = WorkFlowId,
                @OrphanStageOrder = StageOrder
            FROM #OrphanItems
            WHERE RowNum = @OrphanRow;

            BEGIN TRY
                IF @OrphanNextPermId IS NULL
                BEGIN
                    SET @ErrorCount += 1;
                    SET @OrphanRow += 1;
                    CONTINUE;
                END

                -- Get maker for exclusion
                SET @OrphanMakerId = NULL;
                SELECT TOP 1 @OrphanMakerId = h.CreatedBy
                FROM t_WorkFlowHistory h WITH (NOLOCK)
                WHERE h.Source = @OrphanSource
                  AND h.SourceID = @OrphanSourceId
                  AND h.DeletedOn IS NULL
                ORDER BY h.CreatedOn ASC;

                -- Get eligible users
                DECLARE @OrphanUsers TABLE (
                    Id BIGINT PRIMARY KEY,
                    Name NVARCHAR(255),
                    Email NVARCHAR(255)
                );
                DELETE FROM @OrphanUsers;

                INSERT INTO @OrphanUsers (Id, Name, Email)
                SELECT DISTINCT u.Id, u.Name, u.Email
                FROM t_Users u WITH (NOLOCK)
                WHERE u.DeletedOn IS NULL
                  AND u.Id <> ISNULL(@OrphanMakerId, 0)
                  AND u.UserID <> 'CSADM'
                  AND u.Id <> @SystemUserId
                  AND EXISTS (
                      SELECT 1 FROM dbo.f_getUserWithPermission(@OrphanNextPermId) perm
                      WHERE perm.Id = u.Id
                  )
                  AND NOT EXISTS (
                      SELECT 1 FROM t_WorkFlowHistory h WITH (NOLOCK)
                      WHERE h.Source = @OrphanSource
                        AND h.SourceID = @OrphanSourceId
                        AND h.Stage = CAST(@OrphanNextStageId AS NVARCHAR(200))
                        AND h.CreatedBy = u.Id
                        AND h.StatusId = @ApprovedStatusId
                        AND h.DeletedOn IS NULL
                  );

                IF (SELECT COUNT(*) FROM @OrphanUsers) = 0
                BEGIN
                    SET @ErrorCount += 1;
                    SET @OrphanRow += 1;
                    CONTINUE;
                END

                BEGIN TRANSACTION;

                INSERT INTO t_WorkFlowPending (
                    Source, SourceID, Stage, UserId,
                    CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
                )
                SELECT
                    @OrphanSource,
                    @OrphanSourceId,
                    CAST(@OrphanNextStageId AS NVARCHAR(200)),
                    u.Id,
                    @SystemUserId,
                    @Now,
                    @SystemUserId,
                    @Now
                FROM @OrphanUsers u
                WHERE NOT EXISTS (
                    SELECT 1 FROM t_WorkFlowPending p
                    WHERE p.Source = @OrphanSource
                      AND p.SourceID = @OrphanSourceId
                      AND p.Stage = CAST(@OrphanNextStageId AS NVARCHAR(200))
                      AND p.UserId = u.Id
                      AND p.DeletedOn IS NULL
                );

                COMMIT TRANSACTION;

                SET @OrphanCount += 1;

                -- Email notifications for orphan recovery (outside transaction)
                DECLARE orphan_notify_cursor CURSOR LOCAL FAST_FORWARD FOR
                    SELECT Id, Email, Name FROM @OrphanUsers;

                OPEN orphan_notify_cursor;
                FETCH NEXT FROM orphan_notify_cursor INTO @NotifyUserId, @NotifyEmail, @NotifyName;

                WHILE @@FETCH_STATUS = 0
                BEGIN
                    BEGIN TRY
                        IF @NotifyEmail IS NOT NULL AND LEN(@NotifyEmail) > 5
                        BEGIN
                            SET @EmailSubject = 'Workflow Approval Required (Recovery) - ' +
                                ISNULL(@OrphanNextStageName, 'Stage ' + CAST(@OrphanNextStageId AS NVARCHAR(50)));
                            SET @EmailMessage =
                                'Dear ' + ISNULL(@NotifyName, 'User') + ',' + CHAR(13) + CHAR(10) +
                                'A workflow item requires your approval.' + CHAR(13) + CHAR(10) +
                                'Source: ' + ISNULL(@OrphanSource, '[Unknown]') + CHAR(13) + CHAR(10) +
                                'Record ID: ' + ISNULL(@OrphanSourceId, '[Unknown]') + CHAR(13) + CHAR(10) +
                                'Stage: ' + ISNULL(@OrphanNextStageName, '[Unknown]') + CHAR(13) + CHAR(10) +
                                'Please log in to review and take action.';

                            EXEC p_sendNotificationEmail
                                @UserID = @NotifyUserId,
                                @Subject = @EmailSubject,
                                @Message = @EmailMessage,
                                @SenderId = @SystemUserId,
                                @Source = @OrphanSource,
                                @SourceID = @OrphanSourceId;
                        END
                    END TRY
                    BEGIN CATCH
                        -- Email failure should not break recovery
                    END CATCH

                    FETCH NEXT FROM orphan_notify_cursor INTO @NotifyUserId, @NotifyEmail, @NotifyName;
                END

                CLOSE orphan_notify_cursor;
                DEALLOCATE orphan_notify_cursor;

            END TRY
            BEGIN CATCH
                IF @@TRANCOUNT > 0
                    ROLLBACK TRANSACTION;

                SET @ErrorCount += 1;
            END CATCH

            SET @OrphanRow += 1;
        END

        DROP TABLE #OrphanItems;

        -- =================================================================
        -- PHASE 3: CLEANUP — soft-delete pending records where stage
        -- is already fully approved
        -- =================================================================
        UPDATE p
        SET p.DeletedBy = @SystemUserId,
            p.DeletedOn = @Now,
            p.ModifiedBy = @SystemUserId,
            p.ModifiedOn = @Now
        FROM t_WorkFlowPending p
        INNER JOIN t_WorkFlowStages s WITH (NOLOCK)
            ON CAST(p.Stage AS BIGINT) = s.Id
            AND s.DeletedOn IS NULL
        WHERE p.DeletedOn IS NULL
          AND EXISTS (
              SELECT 1
              FROM t_WorkFlowHistory h WITH (NOLOCK)
              WHERE h.Source = p.Source
                AND h.SourceID = p.SourceID
                AND h.Stage = p.Stage
                AND h.StatusId = @ApprovedStatusId
                AND h.DeletedOn IS NULL
              GROUP BY h.Source, h.SourceID, h.Stage
              HAVING COUNT(DISTINCT h.CreatedBy) >= ISNULL(s.[Count], 1)
          );

        SET @CleanupCount = @@ROWCOUNT;

        -- =================================================================
        -- RETURN SUMMARY
        -- =================================================================
        SELECT
            'SUCCESS' AS Status,
            @ProcessedCount AS Processed,
            @AdvancedCount AS Advanced,
            @CompletedCount AS Completed,
            @SkippedCount AS Skipped,
            @OrphanCount AS OrphansRecovered,
            @CleanupCount AS CleanedUp,
            @ErrorCount AS Errors;

    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        IF OBJECT_ID('tempdb..#ApprovalItems') IS NOT NULL
            DROP TABLE #ApprovalItems;
        IF OBJECT_ID('tempdb..#OrphanItems') IS NOT NULL
            DROP TABLE #OrphanItems;

        DECLARE @ErrMsg NVARCHAR(4000) = ERROR_MESSAGE(),
                @ErrSev INT = ERROR_SEVERITY(),
                @ErrState INT = ERROR_STATE();

        RAISERROR(@ErrMsg, @ErrSev, @ErrState);
    END CATCH
END
GO