USE [BR_ERP]
GO

/****** Object:  StoredProcedure [dbo].[p_ProcessWorkflowPendingTest2]    Script Date: 1/6/2026 11:42:21 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE    PROCEDURE [dbo].[p_ProcessWorkflowPendingTest2]
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
    -- Temp table to hold AMT inserts
    CREATE TABLE #AmtInserts (
        Source NVARCHAR(255),
        SourceID NVARCHAR(100),
        Stage NVARCHAR(50),
        UserId BIGINT
    );

    DECLARE @AmtSource NVARCHAR(255), @AmtSourceID NVARCHAR(100), @AmtStage NVARCHAR(50), 
            @StagePermissionId BIGINT;  -- Permission from workflow stage
    
    -- Get AMT records (we'll determine actual permission based on amount)
    DECLARE AmtCursor CURSOR LOCAL FAST_FORWARD FOR
    SELECT 
        h.Source, 
        h.SourceID, 
        CAST(h.StageId AS NVARCHAR(50)), 
        h.PermissionId
    FROM #HistoryToProcess h
    WHERE h.WorkflowType = 'AMT';

    OPEN AmtCursor;
    FETCH NEXT FROM AmtCursor INTO @AmtSource, @AmtSourceID, @AmtStage, @StagePermissionId;

    WHILE @@FETCH_STATUS = 0
    BEGIN
        DECLARE @Amount DECIMAL(18,2) = 0;
        DECLARE @InsertCount INT;
        DECLARE @AmountReference NVARCHAR(255);
        DECLARE @WorkflowLimit DECIMAL(18,2);
        DECLARE @ActualPermissionId BIGINT;  -- Actual permission based on amount
        DECLARE @ColumnExists BIT = 0;
        DECLARE @ErrorMessage NVARCHAR(MAX);
        DECLARE @IdDataType NVARCHAR(100);
        DECLARE @sql NVARCHAR(MAX);

        BEGIN TRY
            -- Get AmountReference from limits table
            SELECT TOP 1 @AmountReference = AmountReference
            FROM t_WorkFlowLimitsTest WITH (NOLOCK)
            WHERE Source = @AmtSource 
              AND DeletedOn IS NULL;

            IF @AmountReference IS NULL OR @AmountReference = ''
            BEGIN
                SET @ErrorMessage = 'AmountReference is not configured for Source: ' + @AmtSource;
                RAISERROR(@ErrorMessage, 16, 1);
                RETURN;
            END

            -- Check if the column exists in the source table
            SET @sql = N'
                IF EXISTS (
                    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = @TableName 
                    AND COLUMN_NAME = @ColumnName
                )
                BEGIN
                    SELECT @ColumnExistsOut = 1;
                END
                ELSE
                BEGIN
                    SELECT @ColumnExistsOut = 0;
                END';

            EXEC sp_executesql @sql,
                N'@TableName NVARCHAR(255), @ColumnName NVARCHAR(255), @ColumnExistsOut BIT OUTPUT',
                @TableName = @AmtSource,
                @ColumnName = @AmountReference,
                @ColumnExistsOut = @ColumnExists OUTPUT;

            IF @ColumnExists = 0
            BEGIN
                SET @ErrorMessage = 'Column "' + @AmountReference + '" does not exist in table "' + @AmtSource + '"';
                RAISERROR(@ErrorMessage, 16, 1);
                RETURN;
            END

            -- Get the data type of the Id column
            SET @sql = N'
                SELECT @DataTypeOut = DATA_TYPE 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_NAME = @TableName 
                  AND COLUMN_NAME = ''Id''';

            EXEC sp_executesql @sql,
                N'@TableName NVARCHAR(255), @DataTypeOut NVARCHAR(100) OUTPUT',
                @TableName = @AmtSource,
                @DataTypeOut = @IdDataType OUTPUT;

            -- Get the amount value with proper type handling
            DECLARE @GetAmountSQL NVARCHAR(MAX);
            
            IF @IdDataType IN ('int', 'bigint', 'smallint', 'tinyint')
            BEGIN
                -- Integer types (t_FinanceInvoices likely uses BIGINT)
                SET @GetAmountSQL = N'
                    SELECT @AmountOut = ISNULL(' + QUOTENAME(@AmountReference) + ', 0)
                    FROM ' + QUOTENAME(@AmtSource) + ' WITH (NOLOCK)
                    WHERE Id = TRY_CAST(@SourceID AS BIGINT)';
            END
            ELSE IF @IdDataType IN ('decimal', 'numeric', 'float', 'real', 'money', 'smallmoney')
            BEGIN
                -- Decimal/numeric types
                SET @GetAmountSQL = N'
                    SELECT @AmountOut = ISNULL(' + QUOTENAME(@AmountReference) + ', 0)
                    FROM ' + QUOTENAME(@AmtSource) + ' WITH (NOLOCK)
                    WHERE Id = TRY_CAST(@SourceID AS DECIMAL(18,2))';
            END
            ELSE
            BEGIN
                -- String types
                SET @GetAmountSQL = N'
                    SELECT @AmountOut = ISNULL(' + QUOTENAME(@AmountReference) + ', 0)
                    FROM ' + QUOTENAME(@AmtSource) + ' WITH (NOLOCK)
                    WHERE Id = @SourceID';
            END

            EXEC sp_executesql @GetAmountSQL,
                N'@SourceID NVARCHAR(100), @AmountOut DECIMAL(18,2) OUTPUT',
                @SourceID = @AmtSourceID,
                @AmountOut = @Amount OUTPUT;

            -- Based on the amount, find the appropriate permission from limits table
            SELECT TOP 1 
                @ActualPermissionId = PermissionId,
                @WorkflowLimit = MaxAmount
            FROM t_WorkFlowLimitsTest WITH (NOLOCK)
            WHERE Source = @AmtSource 
              AND @Amount <= MaxAmount
              AND DeletedOn IS NULL
            ORDER BY MaxAmount;  -- Get smallest limit that covers the amount
            
            -- If no limit found (amount exceeds all limits), get the highest limit
            IF @ActualPermissionId IS NULL
            BEGIN
                SELECT TOP 1 
                    @ActualPermissionId = PermissionId,
                    @WorkflowLimit = MaxAmount
                FROM t_WorkFlowLimitsTest WITH (NOLOCK)
                WHERE Source = @AmtSource 
                  AND DeletedOn IS NULL
                ORDER BY MaxAmount DESC;
            END

            IF @ActualPermissionId IS NULL
            BEGIN
                SET @ErrorMessage = 'No workflow limits configured for Source: ' + @AmtSource;
                RAISERROR(@ErrorMessage, 16, 1);
                RETURN;
       END

            -- Skip if amount exceeds limit (optional - remove if you want to process anyway)
            IF @WorkflowLimit IS NOT NULL AND @Amount > @WorkflowLimit
            BEGIN
                FETCH NEXT FROM AmtCursor INTO @AmtSource, @AmtSourceID, @AmtStage, @StagePermissionId;
                CONTINUE;
            END

            -- ⭐⭐ FIXED: Get approver count from workflow stages for this tier (NO HARDCODED VALUES)
            SELECT @InsertCount = ISNULL(s.[Count], 1)
            FROM t_WorkFlowStagesTest s
            WHERE s.PermissionId = @ActualPermissionId  -- Use the tier permission we found
              AND s.[Order] = 1  -- First stage of this tier
              AND s.DeletedOn IS NULL;

            -- Default to 1 if not found
            IF @InsertCount IS NULL OR @InsertCount < 1
                SET @InsertCount = 1;
            
            -- Optional: Set a maximum limit
            IF @InsertCount > 10
                SET @InsertCount = 10;

            IF @InsertCount > 0
            BEGIN
                INSERT INTO #AmtInserts (Source, SourceID, Stage, UserId)
                SELECT TOP (@InsertCount)
                    @AmtSource, @AmtSourceID, @AmtStage, u.Id
                FROM t_Users u WITH (NOLOCK)
                WHERE u.Id IN (SELECT id FROM f_getUserWithPermission(@ActualPermissionId))  -- Use actual permission!
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

        END TRY
        BEGIN CATCH
            -- Re-raise the error to stop execution
            DECLARE @ErrorMsg NVARCHAR(4000) = ERROR_MESSAGE();
            DECLARE @ErrorSeverity INT = ERROR_SEVERITY();
            DECLARE @ErrorState INT = ERROR_STATE();
            DECLARE @ErrorLine INT = ERROR_LINE();
            
            SET @ErrorMessage = 'Error processing AMT workflow for Source: ' + @AmtSource + 
                               ', SourceID: ' + @AmtSourceID + 
                               ' - ' + @ErrorMsg + 
                               ' (Line: ' + CAST(@ErrorLine AS NVARCHAR(10)) + ')';
            
            RAISERROR(@ErrorMessage, @ErrorSeverity, @ErrorState);
            RETURN;
        END CATCH

        FETCH NEXT FROM AmtCursor INTO @AmtSource, @AmtSourceID, @AmtStage, @StagePermissionId;
    END

    CLOSE AmtCursor;
    DEALLOCATE AmtCursor;

    -- Insert all AMT pending users
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
GO

