USE [BR_ERP]
GO
/****** Object:  StoredProcedure [dbo].[p_ProcessWorkflowPendingTest1]    Script Date: 31/12/2025 17:06:40 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER   PROCEDURE [dbo].[p_ProcessWorkflowPendingTest1]
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE
        @Now DATETIME = GETDATE(),
        @SystemUserId BIGINT;

    -- Get or create SystemUserId using the helper SP
    EXEC dbo.p_getOrCreateSystemUserId @SystemUserId OUTPUT;

    IF @SystemUserId IS NULL
    BEGIN
        RAISERROR('System user not found or could not be created.', 16, 1);
        RETURN;
    END

    -- Temp table to hold the workflow history records that need processing
    CREATE TABLE #HistoryToProcess (
        Source NVARCHAR(255),
        SourceID NVARCHAR(100),
        StageId BIGINT,
        PermissionId BIGINT,
        WorkflowType NVARCHAR(50),
        Count INT
    );

    INSERT INTO #HistoryToProcess (Source, SourceID, StageId, PermissionId, WorkflowType, Count)
    SELECT
        h.Source, h.SourceID, s.Id, s.PermissionId, wt.TypeID, ISNULL(s.[Count], 0)
    FROM t_WorkFlowHistoryTest h WITH (ROWLOCK, READPAST, UPDLOCK)
    INNER JOIN t_WorkFlowsTest wf WITH (NOLOCK) ON h.Source = wf.Source AND wf.DeletedOn IS NULL
    INNER JOIN t_WorkFlowStagesTest s WITH (NOLOCK) ON wf.Id = s.WorkFlowId AND s.[Order] = 1 AND s.DeletedOn IS NULL
    INNER JOIN t_WorkFlowTypes wt WITH (NOLOCK) ON s.WorkFlowTypeId = wt.Id
    WHERE h.IsApproved IS NULL AND h.DeletedOn IS NULL AND h.DeletedBy IS NULL;

    -- Insert ALL workflow type users
    INSERT INTO t_WorkFlowPendingTest (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
    SELECT DISTINCT
        h.Source,
        h.SourceID,
        CAST(h.StageId AS NVARCHAR(50)),
        u.Id,
        @SystemUserId,
        @Now,
        @SystemUserId,
        @Now
    FROM #HistoryToProcess h
    CROSS APPLY (
        SELECT u.Id
        FROM t_Users u WITH (NOLOCK)
        WHERE u.Id IN (SELECT id FROM f_getUserWithPermission(h.PermissionId))
          AND NOT EXISTS (
              SELECT 1 FROM t_WorkFlowPendingTest p WITH (NOLOCK)
              WHERE p.Source = h.Source AND p.SourceID = h.SourceID
                AND p.Stage = CAST(h.StageId AS NVARCHAR(50)) AND p.UserId = u.Id
          )
          AND NOT EXISTS (
              SELECT 1 FROM t_WorkFlowHistoryTest h2 WITH (ROWLOCK, READPAST, UPDLOCK)
              WHERE h2.Source = h.Source AND h2.SourceID = h.SourceID
                AND h2.CreatedBy = u.Id AND h2.IsApproved IS NOT NULL
                AND h2.DeletedOn IS NULL AND h2.DeletedBy IS NULL
          )
    ) u
    WHERE h.WorkflowType = 'ALL';

    -- Insert CNT workflow type users (limit to @Count per record)
    ;WITH CNTUsers AS (
        SELECT
            h.Source, h.SourceID, CAST(h.StageId AS NVARCHAR(50)) AS Stage, h.PermissionId, h.Count,
            u.Id AS UserId,
            ROW_NUMBER() OVER (PARTITION BY h.Source, h.SourceID ORDER BY u.Id) AS rn
        FROM #HistoryToProcess h
        CROSS APPLY (
            SELECT u.Id
            FROM t_Users u WITH (NOLOCK)
            WHERE u.Id IN (SELECT id FROM f_getUserWithPermission(h.PermissionId))
              AND NOT EXISTS (
                  SELECT 1 FROM t_WorkFlowPendingTest p WITH (NOLOCK)
                  WHERE p.Source = h.Source AND p.SourceID = h.SourceID
                    AND p.Stage = CAST(h.StageId AS NVARCHAR(50)) AND p.UserId = u.Id
              )
              AND NOT EXISTS (
                  SELECT 1 FROM t_WorkFlowHistoryTest h2 WITH (ROWLOCK, READPAST, UPDLOCK)
                  WHERE h2.Source = h.Source AND h2.SourceID = h.SourceID
                    AND h2.CreatedBy = u.Id AND h2.IsApproved IS NOT NULL
                    AND h2.DeletedOn IS NULL AND h2.DeletedBy IS NULL
              )
        ) u
        WHERE h.WorkflowType = 'CNT'
    )
    INSERT INTO t_WorkFlowPendingTest (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
    SELECT Source, SourceID, Stage, UserId, @SystemUserId, @Now, @SystemUserId, @Now
    FROM CNTUsers
    WHERE rn <= Count;

    -- Insert MAJ workflow type users (majority = half + 1)
    ;WITH MajUsers AS (
        SELECT
            h.Source, h.SourceID, CAST(h.StageId AS NVARCHAR(50)) AS Stage, h.PermissionId,
            u.Id AS UserId,
            ROW_NUMBER() OVER (PARTITION BY h.Source, h.SourceID ORDER BY u.Id) AS rn,
            COUNT(*) OVER (PARTITION BY h.Source, h.SourceID) AS totalUsers
        FROM #HistoryToProcess h
        CROSS APPLY (
            SELECT u.Id
            FROM t_Users u WITH (NOLOCK)
            WHERE u.Id IN (SELECT id FROM f_getUserWithPermission(h.PermissionId))
              AND NOT EXISTS (
                  SELECT 1 FROM t_WorkFlowPendingTest p WITH (NOLOCK)
                  WHERE p.Source = h.Source AND p.SourceID = h.SourceID
                    AND p.Stage = CAST(h.StageId AS NVARCHAR(50)) AND p.UserId = u.Id
              )
              AND NOT EXISTS (
                  SELECT 1 FROM t_WorkFlowHistoryTest h2 WITH (ROWLOCK, READPAST, UPDLOCK)
                  WHERE h2.Source = h.Source AND h2.SourceID = h.SourceID
                    AND h2.CreatedBy = u.Id AND h2.IsApproved IS NOT NULL
                    AND h2.DeletedOn IS NULL AND h2.DeletedBy IS NULL
              )
        ) u
        WHERE h.WorkflowType = 'MAJ'
    ),
    MajLimited AS (
        SELECT Source, SourceID, Stage, PermissionId, UserId, rn, totalUsers,
               CAST((totalUsers / 2) + 1 AS INT) AS limitUsers
        FROM MajUsers
    )
    INSERT INTO t_WorkFlowPendingTest (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
    SELECT Source, SourceID, Stage, UserId, @SystemUserId, @Now, @SystemUserId, @Now
    FROM MajLimited
    WHERE rn <= limitUsers;

    -- Handle AMT workflow type users (amount-based)
    -- We must fetch Amount dynamically per source record and compare with limits

    -- Temp table to hold AMT inserts
    CREATE TABLE #AmtInserts (
        Source NVARCHAR(255),
        SourceID NVARCHAR(100),
        Stage NVARCHAR(50),
        UserId BIGINT
    );

    DECLARE @AmtSource NVARCHAR(255), @AmtSourceID NVARCHAR(100), @AmtStage NVARCHAR(50), @AmtPermissionId BIGINT;
    DECLARE AmtCursor CURSOR LOCAL FAST_FORWARD FOR
    SELECT Source, SourceID, CAST(StageId AS NVARCHAR(50)), PermissionId
    FROM #HistoryToProcess
    WHERE WorkflowType = 'AMT';

    OPEN AmtCursor;
    FETCH NEXT FROM AmtCursor INTO @AmtSource, @AmtSourceID, @AmtStage, @AmtPermissionId;

    WHILE @@FETCH_STATUS = 0
    BEGIN
        DECLARE @Amount DECIMAL(18,2) = 0;
        DECLARE @WorkflowLimit DECIMAL(18,2);
        DECLARE @InsertCount INT;

        DECLARE @sql NVARCHAR(MAX) = N'
            SELECT @AmountOut = ISNULL(Amount, 0)
            FROM ' + QUOTENAME(@AmtSource) + ' WITH (NOLOCK)
            WHERE Id = @SourceID';

        EXEC sp_executesql @sql,
            N'@SourceID VARCHAR(100), @AmountOut DECIMAL(18,2) OUTPUT',
            @SourceID = @AmtSourceID,
            @AmountOut = @Amount OUTPUT;

        SELECT TOP 1 @WorkflowLimit = MaxAmount
        FROM t_WorkFlowLimitsTest WITH (NOLOCK)
        WHERE Source = @AmtSource AND PermissionId = @AmtPermissionId;

        IF @WorkflowLimit IS NOT NULL AND @Amount > @WorkflowLimit
        BEGIN
            -- Skip this record, amount exceeds limit
            FETCH NEXT FROM AmtCursor INTO @AmtSource, @AmtSourceID, @AmtStage, @AmtPermissionId;
            CONTINUE;
        END

        SET @InsertCount = FLOOR(@Amount);

        IF @InsertCount > 0
        BEGIN
            INSERT INTO #AmtInserts (Source, SourceID, Stage, UserId)
            SELECT TOP (@InsertCount)
                @AmtSource, @AmtSourceID, @AmtStage, u.Id
            FROM t_Users u WITH (NOLOCK)
            WHERE u.Id IN (SELECT id FROM f_getUserWithPermission(@AmtPermissionId))
              AND NOT EXISTS (
                  SELECT 1 FROM t_WorkFlowPendingTest p WITH (NOLOCK)
                  WHERE p.Source = @AmtSource AND p.SourceID = @AmtSourceID
                    AND p.Stage = @AmtStage AND p.UserId = u.Id
              )
              AND NOT EXISTS (
                  SELECT 1 FROM t_WorkFlowHistoryTest h2 WITH (ROWLOCK, READPAST, UPDLOCK)
                  WHERE h2.Source = @AmtSource AND h2.SourceID = @AmtSourceID
                    AND h2.CreatedBy = u.Id AND h2.IsApproved IS NOT NULL
                    AND h2.DeletedOn IS NULL AND h2.DeletedBy IS NULL
              )
            ORDER BY u.Id;
        END

        FETCH NEXT FROM AmtCursor INTO @AmtSource, @AmtSourceID, @AmtStage, @AmtPermissionId;
    END

    CLOSE AmtCursor;
    DEALLOCATE AmtCursor;

    -- Insert all AMT pending users in one go now
    INSERT INTO t_WorkFlowPendingTest (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
    SELECT Source, SourceID, Stage, UserId, @SystemUserId, @Now, @SystemUserId, @Now
    FROM #AmtInserts;

    DROP TABLE #AmtInserts;

    -- Update t_WorkFlowHistoryTest for all processed records
    UPDATE h
    SET DeletedOn = @Now,
        DeletedBy = @SystemUserId
    FROM t_WorkFlowHistoryTest h WITH (ROWLOCK, UPDLOCK)
    INNER JOIN #HistoryToProcess hp ON h.Source = hp.Source AND h.SourceID = hp.SourceID
    WHERE h.IsApproved IS NULL AND h.DeletedOn IS NULL AND h.DeletedBy IS NULL;

    -- Send notification emails for all inserted pending items

    DECLARE @PendingSource NVARCHAR(255),
            @PendingSourceID NVARCHAR(100),
            @PendingStage NVARCHAR(50),
            @PendingUserId BIGINT;

    DECLARE email_cursor CURSOR LOCAL FAST_FORWARD FOR
    SELECT p.Source, p.SourceID, p.Stage, p.UserId
    FROM t_WorkFlowPendingTest p
    INNER JOIN #HistoryToProcess hp ON p.Source = hp.Source AND p.SourceID = hp.SourceID;

    OPEN email_cursor;
    FETCH NEXT FROM email_cursor INTO @PendingSource, @PendingSourceID, @PendingStage, @PendingUserId;

    WHILE @@FETCH_STATUS = 0
    BEGIN
        DECLARE @UserEmail NVARCHAR(255);
        SELECT @UserEmail = Email FROM t_Users WITH (NOLOCK) WHERE Id = @PendingUserId;

        IF @UserEmail IS NOT NULL
        BEGIN
            DECLARE @EmailMessage NVARCHAR(MAX) = 'You have been assigned to a new workflow task for Source: ' + @PendingSource + ', ID: ' + @PendingSourceID;

            EXEC p_sendNotificationEmail
                @UserID = @PendingUserId,
                @Subject = 'You have a new workflow item pending approval',
                @Message = @EmailMessage,
                @SenderId = @SystemUserId,
                @Source = @PendingSource,
                @SourceID = @PendingSourceID;
        END

        FETCH NEXT FROM email_cursor INTO @PendingSource, @PendingSourceID, @PendingStage, @PendingUserId;
    END

    CLOSE email_cursor;
    DEALLOCATE email_cursor;

    DROP TABLE #HistoryToProcess;
END;
