
CREATE OR ALTER   PROCEDURE [dbo].[p_ProcessWorkflowPending]
    @Source NVARCHAR(255) = NULL,
    @SourceID NVARCHAR(100) = NULL,
    @StageID BIGINT = NULL,
    @Amount DECIMAL(20,4) = NULL  -- OPTIONAL - Pass from code for AMT workflows
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

    -- Get system user
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

    -- ========================================
    -- PROCESS SPECIFIC RECORD
    -- ========================================
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
            @PermName NVARCHAR(200);

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
                   'Stage not found for StageID: ' + CAST(@StageID AS NVARCHAR(50)) AS Message;
            RETURN;
        END

        -- Get submitter for maker-checker rule
        SELECT TOP 1 @MakerId = CreatedBy
        FROM t_WorkFlowHistory
        WHERE Source = @Source AND SourceID = @SourceID AND DeletedOn IS NULL
        ORDER BY CreatedOn ASC;

        -- ========================================
        -- TIER SELECTION LOGIC (DYNAMIC - NO HARDCODING)
        -- ========================================
        SET @EffectivePermissionId = @PermissionId; -- Default fallback

        IF @WorkflowType = 'AMT' AND @Amount IS NOT NULL AND @Amount > 0
        BEGIN
            -- Check if tiers exist for this stage
            IF EXISTS (
                SELECT 1 FROM t_WorkFlowLimits 
                WHERE WorkFlowStageId = @StageID AND DeletedOn IS NULL
            )
            BEGIN
                -- Use the function to get the right permission tier
                SELECT TOP 1 
                    @EffectivePermissionId = PermissionId,
                    @TierMaxAmount = MaxAmount,
                    @LimitType = LimitType
                FROM dbo.f_getPermissionForAmount(@StageID, @Amount);

                IF @EffectivePermissionId IS NOT NULL
                BEGIN
                    SELECT @PermName = name FROM t_Permissions WHERE id = @EffectivePermissionId;
                END
                ELSE
                BEGIN
                    SET @EffectivePermissionId = @PermissionId;
                END
            END
        END

        -- ========================================
        -- GET ELIGIBLE USERS - WITH TIER-SPECIFIC PERMISSION FILTERING
        -- ========================================
        DECLARE @EligibleUsers TABLE (
            Id BIGINT PRIMARY KEY, 
            Name NVARCHAR(255), 
            Email NVARCHAR(255)
        );
        
        -- ✅ CRITICAL: Only include users who have THIS SPECIFIC STAGE/TIER PERMISSION
        INSERT INTO @EligibleUsers (Id, Name, Email)
        SELECT DISTINCT u.Id, u.Name, u.Email
        FROM t_Users u
        WHERE u.DeletedOn IS NULL
          AND u.Id <> ISNULL(@MakerId, 0)  -- Maker-checker rule
          -- Restrict CSADM
          AND u.UserID <> 'CSADM'
          -- ✅ KEY: User must have the EFFECTIVE permission (tier-specific for AMT)
          AND EXISTS (
              SELECT 1 FROM dbo.f_getUserWithPermission(@EffectivePermissionId) perm 
              WHERE perm.Id = u.Id
          )
          -- Exclude users who already approved/rejected THIS STAGE
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
        
        IF @EligibleCount = 0
        BEGIN
            SELECT 'ERROR' AS Status,
                   'No eligible approvers found for this stage.' AS Message;
            RETURN;
        END

        -- ========================================
        -- CREATE PENDING APPROVALS - ONLY FOR TIER-ELIGIBLE USERS
        -- ========================================
        -- Clean up existing pending for this stage
        DELETE FROM dbo.t_WorkFlowPending
        WHERE Source = @Source 
          AND SourceID = @SourceID 
          AND Stage = CAST(@StageID AS NVARCHAR(50));

        -- ✅ Insert pending ONLY for users with correct tier/stage permission
        INSERT INTO t_WorkFlowPending (
            Source, SourceID, Stage, UserId, 
            CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
        )
        SELECT 
            @Source, 
            @SourceID, 
            CAST(@StageID AS NVARCHAR(50)), 
            e.Id,
            @SystemUserId, 
            @Now, 
            @SystemUserId, 
            @Now
        FROM @EligibleUsers e;

        SET @ProcessedCount = @@ROWCOUNT;

        -- Mark history as processed
        UPDATE t_WorkFlowHistory
        SET isApproved = 0, ModifiedBy = @SystemUserId, ModifiedOn = @Now
        WHERE Source = @Source 
          AND SourceID = @SourceID 
          AND Stage = CAST(@StageID AS NVARCHAR(50))
          AND StatusId = @SubmittedStatusId
          AND DeletedOn IS NULL
          AND isApproved IS NULL;

        -- ========================================
        -- SEND NOTIFICATIONS (Simplified for brevity)
        -- ========================================
        DECLARE @NotifyUserId BIGINT, @NotifyEmail NVARCHAR(255), @NotifyName NVARCHAR(255);
        DECLARE notify_cursor CURSOR LOCAL FAST_FORWARD FOR 
            SELECT Id, Email, Name FROM @EligibleUsers;
        
        OPEN notify_cursor;
        FETCH NEXT FROM notify_cursor INTO @NotifyUserId, @NotifyEmail, @NotifyName;
        
        WHILE @@FETCH_STATUS = 0
        BEGIN
            -- Notification logic here (omitted mostly as it was in original)
            FETCH NEXT FROM notify_cursor INTO @NotifyUserId, @NotifyEmail, @NotifyName;
        END
        
        CLOSE notify_cursor;
        DEALLOCATE notify_cursor;
        
        SELECT 
            'SUCCESS' AS Status, 
            @ProcessedCount AS InsertedPendingCount, 
            @CountRequired AS ApprovalsRequired,
            @EffectivePermissionId AS EffectivePermissionId,
            0 AS Errors;
        RETURN;
    END

    -- ========================================
    -- BATCH PROCESSING (backward compatibility)
    -- ========================================
    IF OBJECT_ID('tempdb..#WorkSet') IS NOT NULL DROP TABLE #WorkSet;
    CREATE TABLE #WorkSet (
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

    ;WITH LatestPending AS (
        SELECT h.Id, h.Source, h.SourceID, h.Stage, h.CreatedBy,
               ROW_NUMBER() OVER (PARTITION BY h.Source, h.SourceID ORDER BY h.CreatedOn DESC) as rn
        FROM t_WorkFlowHistory h
        WHERE h.DeletedOn IS NULL 
          AND h.isApproved IS NULL 
          AND h.StatusId = @SubmittedStatusId
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
          WHERE p.Source = lp.Source 
            AND p.SourceID = lp.SourceID 
            AND p.Stage = lp.Stage 
            AND p.DeletedOn IS NULL
      );

    DECLARE @BatchCount INT = (SELECT COUNT(*) FROM #WorkSet);

    DECLARE @HistoryId BIGINT, @SourceLocal NVARCHAR(255), @SourceIDLocal NVARCHAR(100),
            @StageIdLocal BIGINT, @PermIdLocal BIGINT, @WFType NVARCHAR(20), 
            @CountReq INT, @MakerLocal BIGINT, @StageNameLocal NVARCHAR(255);

    DECLARE ws_cursor CURSOR LOCAL FAST_FORWARD FOR
        SELECT HistoryId, Source, SourceID, StageId, PermissionId, WorkflowType, CountRequired, MakerId, StageName 
        FROM #WorkSet;

    OPEN ws_cursor;
    FETCH NEXT FROM ws_cursor INTO @HistoryId, @SourceLocal, @SourceIDLocal, @StageIdLocal, 
                                     @PermIdLocal, @WFType, @CountReq, @MakerLocal, @StageNameLocal;

    WHILE @@FETCH_STATUS = 0
    BEGIN
        BEGIN TRY
            DECLARE @EffPermBatch BIGINT;
            SET @EffPermBatch = @PermIdLocal;

            IF @PermIdLocal IS NULL
            BEGIN
                SET @ErrorCount += 1;
                GOTO NextBatchRow;
            END

            -- Get eligible users
            DECLARE @EligibleBatch TABLE (Id BIGINT PRIMARY KEY);
            DELETE FROM @EligibleBatch;
            
            INSERT INTO @EligibleBatch (Id)
            SELECT DISTINCT u.Id
            FROM t_Users u
            WHERE u.DeletedOn IS NULL
              AND EXISTS (
                  SELECT 1 FROM dbo.f_getUserWithPermission(@EffPermBatch) 
                  WHERE Id = u.Id
              )
              AND u.Id <> ISNULL(@MakerLocal, 0)
              -- RESTRICT CSADM IN BATCH MODE TOO
              AND u.UserID <> 'CSADM'
              AND NOT EXISTS (
                  SELECT 1 FROM dbo.t_WorkFlowHistory h
                  WHERE h.Source = @SourceLocal 
                    AND h.SourceID = @SourceIDLocal 
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

            -- Clean and insert pending
            DELETE FROM dbo.t_WorkFlowPending
            WHERE Source = @SourceLocal 
              AND SourceID = @SourceIDLocal 
              AND Stage = CAST(@StageIdLocal AS NVARCHAR(50));

            INSERT INTO t_WorkFlowPending (
                Source, SourceID, Stage, UserId, 
                CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
            )
            SELECT 
                @SourceLocal, 
                @SourceIDLocal, 
                CAST(@StageIdLocal AS NVARCHAR(50)), 
                e.Id,
                @SystemUserId, 
                @Now, 
                @SystemUserId, 
                @Now
            FROM @EligibleBatch e;

            SET @ProcessedCount += @@ROWCOUNT;

            IF @HistoryId IS NOT NULL
                UPDATE t_WorkFlowHistory 
                SET isApproved = 0, ModifiedBy = @SystemUserId, ModifiedOn = @Now 
                WHERE Id = @HistoryId;

        END TRY
        BEGIN CATCH
            SET @ErrorCount += 1;
        END CATCH

NextBatchRow:
        FETCH NEXT FROM ws_cursor INTO @HistoryId, @SourceLocal, @SourceIDLocal, @StageIdLocal, 
                                         @PermIdLocal, @WFType, @CountReq, @MakerLocal, @StageNameLocal;
    END

    CLOSE ws_cursor;
    DEALLOCATE ws_cursor;
    DROP TABLE IF EXISTS #WorkSet;

    SELECT 'SUCCESS' AS Status, @ProcessedCount AS InsertedPendingCount, @ErrorCount AS Errors;
END;
