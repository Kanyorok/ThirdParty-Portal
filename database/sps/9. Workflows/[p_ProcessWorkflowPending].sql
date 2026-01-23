CREATE OR ALTER PROCEDURE [dbo].[p_ProcessWorkflowPending]
    @Source NVARCHAR(255) = NULL,
    @SourceID NVARCHAR(100) = NULL,
    @StageID BIGINT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE
        @Now DATETIME = GETDATE(),
        @SystemUserId BIGINT,
        @SubmittedStatusId BIGINT,
        @ApprovedStatusId BIGINT,
        @RejectedStatusId BIGINT,
        @ProcessedCount INT = 0,
        @ErrorCount INT = 0;

    -- Resolve system user
    SELECT TOP 1 @SystemUserId = Id
    FROM t_Users WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

    IF @SystemUserId IS NULL
    BEGIN
        SELECT 'ERROR' AS Status, 'System user not found.' AS Message;
        RETURN;
    END

    -- Get workflow status IDs
    SELECT @SubmittedStatusId = id FROM t_codeDetails WHERE [Description] = 'Submitted for Approval';
    SELECT @ApprovedStatusId  = id FROM t_codeDetails WHERE [Description] = 'Approved';
    SELECT @RejectedStatusId  = id FROM t_codeDetails WHERE [Description] = 'Rejected';

    IF @SubmittedStatusId IS NULL OR @ApprovedStatusId IS NULL OR @RejectedStatusId IS NULL
    BEGIN
        SELECT 'ERROR' AS Status, 'Required workflow status IDs are missing.' AS Message;
        RETURN;
    END

    -- ========================================
    -- PROCESS SPECIFIC RECORD
    -- ========================================
    IF @Source IS NOT NULL AND @SourceID IS NOT NULL AND @StageID IS NOT NULL
    BEGIN
        PRINT '=== PROCESSING SPECIFIC RECORD ===';
        PRINT 'Source: ' + @Source + ', SourceID: ' + @SourceID + ', StageID: ' + CAST(@StageID AS NVARCHAR(50));

        DECLARE
            @PermissionId BIGINT,
            @WorkflowType NVARCHAR(50),
            @CountRequired INT,
            @MakerId BIGINT,
            @WorkFlowID BIGINT,
            @StageName NVARCHAR(255),
            @Amount DECIMAL(20,4) = 0,
            @EffectivePermissionId BIGINT,
            @LimitType NVARCHAR(20) = 'DEFAULT';

        -- Get stage details
        SELECT
            @PermissionId = s.PermissionId,
            @WorkflowType = wt.TypeId,
            @CountRequired = ISNULL(s.[Count], 1),
            @WorkFlowID = s.WorkFlowId,
            @StageName = s.StageName
        FROM t_WorkFlowStages s
        LEFT JOIN t_WorkFlowTypes wt ON s.WorkFlowTypeId = wt.Id
        WHERE s.Id = @StageID AND s.DeletedOn IS NULL;

        IF @PermissionId IS NULL
        BEGIN
            SELECT 'ERROR' AS Status,
                   'Stage not found or no permission configured for StageID: ' + CAST(@StageID AS NVARCHAR(50)) AS Message;
            RETURN;
        END

        -- Get submitter for maker-checker rule
        SELECT TOP 1 @MakerId = CreatedBy
        FROM t_WorkFlowHistory
        WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL
        ORDER BY CreatedOn ASC;

        -- AMOUNT-BASED PERMISSION SELECTION
        SET @EffectivePermissionId = @PermissionId;

        -- Try to get amount from source table
        BEGIN TRY
            DECLARE @sql NVARCHAR(MAX) = N'
                SELECT @AmountOut = ISNULL(Amount, 0)
                FROM ' + QUOTENAME(@Source) + '
                WHERE Id = CAST(@SourceID AS BIGINT)';
            EXEC sp_executesql @sql,
                N'@SourceID NVARCHAR(100), @AmountOut DECIMAL(20,4) OUTPUT',
                @SourceID, @Amount OUTPUT;
        END TRY
        BEGIN CATCH
            SET @Amount = 0;
        END CATCH

        -- Check if workflow limits exist for this stage
        IF EXISTS (
            SELECT 1 FROM t_WorkFlowLimits
            WHERE WorkFlowStageId = @StageID AND DeletedOn IS NULL
        )
        BEGIN
            SELECT TOP 1 @EffectivePermissionId = PermissionId, @LimitType = 'COVERING'
            FROM t_WorkFlowLimits
            WHERE WorkFlowStageId = @StageID
              AND DeletedOn IS NULL
              AND @Amount <= MaxAmount
            ORDER BY MaxAmount ASC;

            IF @EffectivePermissionId IS NULL OR @LimitType <> 'COVERING'
            BEGIN
                SELECT TOP 1 @EffectivePermissionId = PermissionId, @LimitType = 'EXCEEDED'
                FROM t_WorkFlowLimits
                WHERE WorkFlowStageId = @StageID AND DeletedOn IS NULL
                ORDER BY MaxAmount DESC;
            END

            IF @EffectivePermissionId IS NULL
                SET @EffectivePermissionId = @PermissionId;
        END

        -- GET ELIGIBLE USERS
        DECLARE @EligibleUsers TABLE (Id BIGINT PRIMARY KEY, Name NVARCHAR(255), Email NVARCHAR(255));

        INSERT INTO @EligibleUsers (Id, Name, Email)
        SELECT DISTINCT u.Id, u.Name, u.Email
        FROM t_Users u
        WHERE u.DeletedOn IS NULL
          AND EXISTS (
              SELECT 1 FROM dbo.f_getUserWithPermission(@EffectivePermissionId) perm
              WHERE perm.Id = u.Id
          )
          AND u.Id <> ISNULL(@MakerId, 0)
          AND NOT EXISTS (
              SELECT 1 FROM dbo.t_WorkFlowHistory h
              WHERE h.Source = @Source
                AND h.SourceID = @SourceID
                AND h.Stage = CAST(@StageID AS NVARCHAR(50))
                AND h.CreatedBy = u.Id
                AND h.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
                AND h.DeletedOn IS NULL
          );

        DECLARE @EligibleCount INT = (SELECT COUNT(*) FROM @EligibleUsers);
        PRINT 'Eligible users found: ' + CAST(@EligibleCount AS NVARCHAR(10));

        IF @EligibleCount = 0
        BEGIN
            SELECT 'ERROR' AS Status,
                   'No eligible approvers found. PermissionId: ' + CAST(@EffectivePermissionId AS NVARCHAR(50)) AS Message;
            RETURN;
        END

        -- Clean up existing pending
        DELETE FROM dbo.t_WorkFlowPending
        WHERE Source = @Source AND SourceID = @SourceID AND Stage = CAST(@StageID AS NVARCHAR(50));

        -- Insert pending approvals for ALL eligible users (Pool Logic)
        INSERT INTO t_WorkFlowPending (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
        SELECT @Source, @SourceID, CAST(@StageID AS NVARCHAR(50)), e.Id,
               @SystemUserId, @Now, @SystemUserId, @Now
        FROM @EligibleUsers e;

        SET @ProcessedCount = @@ROWCOUNT;
        PRINT '✓ Inserted ' + CAST(@ProcessedCount AS NVARCHAR(10)) + ' pending approvals';

        -- Mark history as processed
        UPDATE t_WorkFlowHistory
        SET isApproved = 0, ModifiedBy = @SystemUserId, ModifiedOn = @Now
        WHERE Source = @Source
          AND SourceID = @SourceID
          AND Stage = CAST(@StageID AS NVARCHAR(50))
          AND StatusId = @SubmittedStatusId
          AND DeletedOn IS NULL
          AND isApproved IS NULL;

        -- Send notifications
        DECLARE @NewUserId BIGINT, @NewUserEmail NVARCHAR(255), @NewUserName NVARCHAR(255);
        DECLARE new_user_cursor CURSOR LOCAL FAST_FORWARD FOR
            SELECT p.UserId, u.Email, u.Name
            FROM t_WorkFlowPending p
            JOIN t_Users u ON p.UserId = u.Id
            WHERE p.Source = @Source
              AND p.SourceID = @SourceID
              AND p.Stage = CAST(@StageID AS NVARCHAR(50))
              AND p.DeletedOn IS NULL;

        OPEN new_user_cursor;
        FETCH NEXT FROM new_user_cursor INTO @NewUserId, @NewUserEmail, @NewUserName;

        WHILE @@FETCH_STATUS = 0
        BEGIN
            BEGIN TRY
                DECLARE @NotificationMessage NVARCHAR(MAX) =
                    'You have been assigned to a new workflow task. Source: ' + @Source +
                    ', ID: ' + @SourceID + '. Stage: ' + ISNULL(@StageName, CAST(@StageID AS NVARCHAR(50))) + '.';

                EXEC p_sendNotificationEmail
                    @UserID = @NewUserId,
                    @Subject = 'New Workflow Item Pending Approval',
                    @Message = @NotificationMessage,
                    @SenderId = @SystemUserId,
                    @Source = @Source,
                    @SourceID = @SourceID;
            END TRY
            BEGIN CATCH
                PRINT 'Warning: Failed to notify UserID ' + CAST(@NewUserId AS NVARCHAR(50));
            END CATCH

            FETCH NEXT FROM new_user_cursor INTO @NewUserId, @NewUserEmail, @NewUserName;
        END

        CLOSE new_user_cursor;
        DEALLOCATE new_user_cursor;

        PRINT '✓ SPECIFIC RECORD PROCESSING COMPLETE';
        SELECT 'SUCCESS' AS Status,
               @ProcessedCount AS InsertedPendingCount,
               @CountRequired AS ApprovalsRequired,
               @EffectivePermissionId AS EffectivePermissionId,
               @LimitType AS LimitType,
               0 AS Errors;
        RETURN;
    END

    -- ========================================
    -- BATCH PROCESSING (backward compatibility)
    -- ========================================
    PRINT 'BATCH PROCESSING MODE';

    IF OBJECT_ID('tempdb..#WorkSet') IS NOT NULL DROP TABLE #WorkSet;
    CREATE TABLE #WorkSet (
        HistoryId BIGINT, Source NVARCHAR(255), SourceID NVARCHAR(100),
        StageId BIGINT, PermissionId BIGINT, WorkflowType NVARCHAR(20),
        CountRequired INT, MakerId BIGINT, StageName NVARCHAR(255)
    );

    ;WITH LatestPending AS (
        SELECT h.Id, h.Source, h.SourceID, h.Stage, h.CreatedBy,
               ROW_NUMBER() OVER (PARTITION BY h.Source, h.SourceID ORDER BY h.CreatedOn DESC) as rn
        FROM t_WorkFlowHistory h
        WHERE h.DeletedOn IS NULL AND h.isApproved IS NULL AND h.StatusId = @SubmittedStatusId
    )
    INSERT INTO #WorkSet
    SELECT lp.Id, lp.Source, lp.SourceID, CAST(lp.Stage AS BIGINT),
           s.PermissionId, wt.TypeId, ISNULL(s.[Count], 1), lp.CreatedBy, s.StageName
    FROM LatestPending lp
    JOIN t_WorkFlowStages s ON s.Id = CAST(lp.Stage AS BIGINT) AND s.DeletedOn IS NULL
    LEFT JOIN t_WorkFlowTypes wt ON s.WorkFlowTypeId = wt.Id
    WHERE lp.rn = 1
      AND NOT EXISTS (
          SELECT 1 FROM t_WorkFlowPending p
          WHERE p.Source = lp.Source AND p.SourceID = lp.SourceID
            AND p.Stage = lp.Stage AND p.DeletedOn IS NULL
      );

    DECLARE @BatchCount INT = (SELECT COUNT(*) FROM #WorkSet);
    PRINT 'Batch records: ' + CAST(@BatchCount AS NVARCHAR(10));

    DECLARE @HistoryId BIGINT, @SourceLocal NVARCHAR(255), @SourceIDLocal NVARCHAR(100),
            @StageIdLocal BIGINT, @PermIdLocal BIGINT, @WFType NVARCHAR(20),
            @CountReq INT, @MakerLocal BIGINT, @StageNameLocal NVARCHAR(255);

    DECLARE ws_cursor CURSOR LOCAL FAST_FORWARD FOR
        SELECT HistoryId, Source, SourceID, StageId, PermissionId, WorkflowType, CountRequired, MakerId, StageName
        FROM #WorkSet;

    OPEN ws_cursor;
    FETCH NEXT FROM ws_cursor INTO @HistoryId, @SourceLocal, @SourceIDLocal, @StageIdLocal, @PermIdLocal, @WFType, @CountReq, @MakerLocal, @StageNameLocal;

    WHILE @@FETCH_STATUS = 0
    BEGIN
        BEGIN TRY
            DECLARE @AmtBatch DECIMAL(20,4) = 0, @EffPermBatch BIGINT, @LimitTypeBatch NVARCHAR(20) = 'DEFAULT';
            SET @EffPermBatch = @PermIdLocal;

            IF @PermIdLocal IS NULL
            BEGIN
                SET @ErrorCount += 1;
                GOTO NextBatchRow;
            END

            BEGIN TRY
                DECLARE @sqlBatch NVARCHAR(MAX) = N'SELECT @AmountOut = ISNULL(Amount,0) FROM ' + QUOTENAME(@SourceLocal) + ' WHERE Id = CAST(@SourceID AS BIGINT)';
                EXEC sp_executesql @sqlBatch, N'@SourceID NVARCHAR(100), @AmountOut DECIMAL(20,4) OUTPUT', @SourceIDLocal, @AmtBatch OUTPUT;
            END TRY
            BEGIN CATCH
                SET @AmtBatch = 0;
            END CATCH

            IF EXISTS (SELECT 1 FROM t_WorkFlowLimits WHERE WorkFlowStageId = @StageIdLocal AND DeletedOn IS NULL)
            BEGIN
                SELECT TOP 1 @EffPermBatch = PermissionId, @LimitTypeBatch = 'COVERING'
                FROM t_WorkFlowLimits
                WHERE WorkFlowStageId = @StageIdLocal AND DeletedOn IS NULL AND @AmtBatch <= MaxAmount
                ORDER BY MaxAmount ASC;

                IF @EffPermBatch IS NULL OR @LimitTypeBatch <> 'COVERING'
                BEGIN
                    SELECT TOP 1 @EffPermBatch = PermissionId, @LimitTypeBatch = 'EXCEEDED'
                    FROM t_WorkFlowLimits WHERE WorkFlowStageId = @StageIdLocal AND DeletedOn IS NULL
                    ORDER BY MaxAmount DESC;
                END

                IF @EffPermBatch IS NULL SET @EffPermBatch = @PermIdLocal;
            END

            DECLARE @EligibleBatch TABLE (Id BIGINT PRIMARY KEY);
            DELETE FROM @EligibleBatch;

            INSERT INTO @EligibleBatch (Id)
            SELECT DISTINCT u.Id
            FROM t_Users u
            WHERE u.DeletedOn IS NULL
              AND EXISTS (SELECT 1 FROM dbo.f_getUserWithPermission(@EffPermBatch) WHERE Id = u.Id)
              AND u.Id <> ISNULL(@MakerLocal, 0)
              AND NOT EXISTS (
                  SELECT 1 FROM dbo.t_WorkFlowHistory h
                  WHERE h.Source = @SourceLocal AND h.SourceID = @SourceIDLocal
                    AND h.Stage = CAST(@StageIdLocal AS NVARCHAR(50))
                    AND h.CreatedBy = u.Id
                    AND h.StatusId IN (@ApprovedStatusId, @RejectedStatusId)
                    AND h.DeletedOn IS NULL
              );

            DECLARE @EligBatchCnt INT = (SELECT COUNT(*) FROM @EligibleBatch);

            IF @EligBatchCnt = 0
            BEGIN
                SET @ErrorCount += 1;
                GOTO NextBatchRow;
            END

            DELETE FROM dbo.t_WorkFlowPending
            WHERE Source = @SourceLocal AND SourceID = @SourceIDLocal AND Stage = CAST(@StageIdLocal AS NVARCHAR(50));

            -- Insert for ALL eligible users (Pool Logic)
            INSERT INTO t_WorkFlowPending (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
            SELECT @SourceLocal, @SourceIDLocal, CAST(@StageIdLocal AS NVARCHAR(50)), e.Id,
                   @SystemUserId, @Now, @SystemUserId, @Now
            FROM @EligibleBatch e;

            SET @ProcessedCount += @@ROWCOUNT;

            IF @HistoryId IS NOT NULL
                UPDATE t_WorkFlowHistory SET isApproved = 0, ModifiedBy = @SystemUserId, ModifiedOn = @Now WHERE Id = @HistoryId;

        END TRY
        BEGIN CATCH
            SET @ErrorCount += 1;
        END CATCH

NextBatchRow:
        FETCH NEXT FROM ws_cursor INTO @HistoryId, @SourceLocal, @SourceIDLocal, @StageIdLocal, @PermIdLocal, @WFType, @CountReq, @MakerLocal, @StageNameLocal;
    END

    CLOSE ws_cursor;
    DEALLOCATE ws_cursor;
    DROP TABLE IF EXISTS #WorkSet;

    PRINT 'Processed: ' + CAST(@ProcessedCount AS NVARCHAR(20)) + ', Errors: ' + CAST(@ErrorCount AS NVARCHAR(20));
    SELECT 'SUCCESS' AS Status, @ProcessedCount AS InsertedPendingCount, @ErrorCount AS Errors;
END;
