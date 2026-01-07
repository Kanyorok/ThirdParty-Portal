USE [BR_ERP]
GO

/****** Object:  StoredProcedure [dbo].[p_ProcessWorkflowStagesTest2]    Script Date: 1/6/2026 11:42:46 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE   PROCEDURE [dbo].[p_ProcessWorkflowStagesTest2]
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE 
        @SystemUserId BIGINT,
        @Now DATETIME = GETDATE(),
        @ApprovedStatusId BIGINT,
        @TotalProcessed INT = 0,
        @TotalMoved INT = 0,
        @TotalCreated INT = 0;

    -- Get or create SystemUserId
    EXEC dbo.p_getOrCreateSystemUserId @SystemUserId OUTPUT;

    IF @SystemUserId IS NULL
    BEGIN
        RAISERROR('System user not found or could not be created.', 16, 1);
        RETURN;
    END

    -- Get the Approved status
    SELECT @ApprovedStatusId = ID
    FROM t_CodeDetails
    WHERE Description IN ('Approved', 'Approval');

    IF @ApprovedStatusId IS NULL
    BEGIN
        RAISERROR('Approved status not found in t_CodeDetails.', 16, 1);
        RETURN;
    END

    PRINT '=== STARTING WORKFLOW STAGE PROCESSING ===';
    PRINT 'System User: ' + CAST(@SystemUserId AS NVARCHAR(20));
    PRINT 'Approved Status ID: ' + CAST(@ApprovedStatusId AS NVARCHAR(20));

    -- ===================================================================
    -- PHASE 1: Process ACTIVE pending items (check if they're complete)
    -- ===================================================================
    PRINT CHAR(13) + '--- PHASE 1: Processing Active Pending Items ---';
    
    DECLARE @SourceTable NVARCHAR(255);
    
    DECLARE source_cursor CURSOR LOCAL FAST_FORWARD FOR
    SELECT DISTINCT Source 
    FROM t_WorkFlowPendingTest 
    WHERE DeletedOn IS NULL
    ORDER BY Source;

    OPEN source_cursor;
    FETCH NEXT FROM source_cursor INTO @SourceTable;

    WHILE @@FETCH_STATUS = 0
    BEGIN
        PRINT 'Processing Source: ' + @SourceTable;
        
        DECLARE @SourceProcessed INT = 0, @SourceMoved INT = 0;
        
        -- Process each item in this source table
        DECLARE @SourceID NVARCHAR(100), @CurrentStageId BIGINT;
        
        DECLARE item_cursor CURSOR LOCAL FAST_FORWARD FOR
        SELECT DISTINCT 
            SourceID, 
            CAST(Stage AS BIGINT) as StageId
        FROM t_WorkFlowPendingTest 
        WHERE Source = @SourceTable 
          AND DeletedOn IS NULL
        ORDER BY SourceID;

        OPEN item_cursor;
        FETCH NEXT FROM item_cursor INTO @SourceID, @CurrentStageId;

        WHILE @@FETCH_STATUS = 0
        BEGIN
            SET @SourceProcessed = @SourceProcessed + 1;
            
            DECLARE @CurrentOrder INT, @PermissionId BIGINT, @WorkflowType NVARCHAR(100),
                    @Count INT, @ActualApprovals INT, @RequiredApprovals INT,
                    @NextStageId BIGINT, @ErrorMessage NVARCHAR(MAX);

            BEGIN TRY
                -- Get current stage metadata
                SELECT 
                    @CurrentOrder = s.[Order], 
                    @PermissionId = s.PermissionId,
                    @WorkflowType = wt.TypeID,
                    @Count = ISNULL(s.[Count], 0)
                FROM t_WorkFlowStagesTest s
                INNER JOIN t_WorkFlowTypes wt ON s.WorkFlowTypeId = wt.Id
                WHERE s.Id = @CurrentStageId
                  AND s.DeletedOn IS NULL;

                IF @CurrentOrder IS NULL
                BEGIN
                    PRINT '  WARNING: Stage ' + CAST(@CurrentStageId AS NVARCHAR(10)) + ' not found for ' + @SourceID;
                    FETCH NEXT FROM item_cursor INTO @SourceID, @CurrentStageId;
                    CONTINUE;
                END

                -- Count actual approvals for THIS item
                SELECT @ActualApprovals = COUNT(DISTINCT h.CreatedBy)
                FROM t_WorkFlowHistoryTest h
                WHERE h.Source = @SourceTable
                  AND h.SourceID = @SourceID
                  AND LTRIM(RTRIM(h.Stage)) = LTRIM(RTRIM(CAST(@CurrentStageId AS NVARCHAR(50))))
                  AND h.IsApproved = 1
                  AND h.DeletedOn IS NULL;

                -- Calculate required approvals based on workflow type
                SET @RequiredApprovals = @Count;  -- Default for CNT and AMT

                IF @WorkflowType = 'ALL'
                BEGIN
                    SELECT @RequiredApprovals = COUNT(*)
                    FROM t_Users u
                    WHERE u.DeletedOn IS NULL
                      AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@PermissionId));
                END
                ELSE IF @WorkflowType = 'MAJ'
                BEGIN
                    DECLARE @TotalUsers INT;
                    SELECT @TotalUsers = COUNT(*)
                    FROM t_Users u
                    WHERE u.DeletedOn IS NULL
                      AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@PermissionId));
                    
                    SET @RequiredApprovals = CEILING(@TotalUsers * 1.0 / 2);
                END

                -- Debug output
                PRINT '  Item: ' + @SourceID + 
                      ' | Stage: ' + CAST(@CurrentStageId AS NVARCHAR(10)) + 
                      ' (Order: ' + CAST(@CurrentOrder AS NVARCHAR(10)) + ')' +
                      ' | Type: ' + @WorkflowType +
                      ' | Actual: ' + CAST(@ActualApprovals AS NVARCHAR(10)) + 
                      ' | Required: ' + CAST(@RequiredApprovals AS NVARCHAR(10));

                -- If enough approvals, move to next stage
                IF @RequiredApprovals > 0 AND @ActualApprovals >= @RequiredApprovals
                BEGIN
                    -- FIX: Find next stage by ORDER only, not PermissionId
                    SELECT TOP 1 @NextStageId = s2.Id
                    FROM t_WorkFlowStagesTest s2
                    WHERE s2.[Order] = @CurrentOrder + 1  -- CHANGED: Removed PermissionId check
                      AND s2.DeletedOn IS NULL
                    ORDER BY s2.Id;

                    -- If next stage does not exist, workflow is complete
                    IF @NextStageId IS NULL
                    BEGIN
                        PRINT '    → WORKFLOW COMPLETE for ' + @SourceTable + ' ID: ' + @SourceID;
                        
                        -- Mark current pending as deleted
                        UPDATE t_WorkFlowPendingTest
                        SET DeletedOn = @Now,
                            DeletedBy = @SystemUserId
                        WHERE Source = @SourceTable
                          AND SourceID = @SourceID
                          AND Stage = CAST(@CurrentStageId AS NVARCHAR(50))
                          AND DeletedOn IS NULL;
                          
                        SET @SourceMoved = @SourceMoved + 1;
                        SET @TotalMoved = @TotalMoved + 1;
                    END
                    ELSE
                    BEGIN
                        -- Get next stage metadata
                        DECLARE @NextPermissionId BIGINT, @NextWorkflowType NVARCHAR(100),
                                @NextCount INT, @NextStageApproversNeeded INT;
                        
                        SELECT TOP 1
                            @NextPermissionId = s2.PermissionId,
                            @NextWorkflowType = wt2.TypeID,
                            @NextCount = ISNULL(s2.[Count], 0)
                        FROM t_WorkFlowStagesTest s2
                        JOIN t_WorkFlowTypes wt2 ON s2.WorkFlowTypeId = wt2.Id
                        WHERE s2.Id = @NextStageId
                          AND s2.DeletedOn IS NULL;

                        -- Calculate approvers needed for next stage
                        SET @NextStageApproversNeeded = @NextCount;

                        -- Ensure valid count
                        IF @NextStageApproversNeeded < 1 
                            SET @NextStageApproversNeeded = 1;
                        IF @NextStageApproversNeeded > 10 
                            SET @NextStageApproversNeeded = 10;

                        -- FIX: Check if next stage is already complete before creating tasks
                        DECLARE @NextStageActualApprovals INT;
                        SELECT @NextStageActualApprovals = COUNT(DISTINCT h.CreatedBy)
                        FROM t_WorkFlowHistoryTest h
                        WHERE h.Source = @SourceTable
                          AND h.SourceID = @SourceID
                          AND h.Stage = CAST(@NextStageId AS NVARCHAR(50))
                          AND h.IsApproved = 1
                          AND h.DeletedOn IS NULL;

                        PRINT '    Checking Next Stage ' + CAST(@NextStageId AS NVARCHAR(10)) + 
                              ': Required=' + CAST(@NextStageApproversNeeded AS NVARCHAR(10)) +
                              ', Actual=' + CAST(@NextStageActualApprovals AS NVARCHAR(10));

                        -- Only create tasks if next stage is NOT already complete
                        IF @NextStageActualApprovals < @NextStageApproversNeeded
                        BEGIN
                            -- Create next stage pending tasks
                            INSERT INTO t_WorkFlowPendingTest (
                                Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
                            )
                            SELECT TOP (@NextStageApproversNeeded)
                                @SourceTable, 
                                @SourceID, 
                                CAST(@NextStageId AS NVARCHAR(50)), 
                                u.Id, 
                                @SystemUserId, 
                                @Now, 
                                @SystemUserId, 
                                @Now
                            FROM t_Users u
                            WHERE u.DeletedOn IS NULL
                              AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@NextPermissionId))
                              AND NOT EXISTS (
                                  -- Exclude users who already approved this item
                                  SELECT 1 
                                  FROM t_WorkFlowHistoryTest h
                                  WHERE h.Source = @SourceTable
                                    AND h.SourceID = @SourceID
                                    AND h.CreatedBy = u.Id
                                    AND h.IsApproved = 1
                                    AND h.DeletedOn IS NULL
                              )
                              AND NOT EXISTS (
                                  SELECT 1 FROM t_WorkFlowPendingTest p
                                  WHERE p.Source = @SourceTable
                                    AND p.SourceID = @SourceID
                                    AND p.Stage = CAST(@NextStageId AS NVARCHAR(50))
                                    AND p.UserId = u.Id
                                    AND p.DeletedOn IS NULL
                              )
                              AND u.Id != 1  -- Don't assign to system user
                            ORDER BY u.Id;
                            
                            DECLARE @RowsInserted INT = @@ROWCOUNT;
                            SET @TotalCreated = @TotalCreated + @RowsInserted;
                            
                            PRINT '    → MOVED to Stage ' + CAST(@NextStageId AS NVARCHAR(10)) + 
                                  ' (Order: ' + CAST(@CurrentOrder + 1 AS NVARCHAR(10)) + ')' +
                                  ' (' + CAST(@RowsInserted AS NVARCHAR(10)) + ' users assigned)';
                        END
                        ELSE
                        BEGIN
                            PRINT '    → Next stage already complete, skipping task creation';
                        END
                        
                        -- Mark current pending as deleted
                        UPDATE t_WorkFlowPendingTest
                        SET DeletedOn = @Now,
                            DeletedBy = @SystemUserId
                        WHERE Source = @SourceTable
                          AND SourceID = @SourceID
                          AND Stage = CAST(@CurrentStageId AS NVARCHAR(50))
                          AND DeletedOn IS NULL;
                          
                        SET @SourceMoved = @SourceMoved + 1;
                        SET @TotalMoved = @TotalMoved + 1;
                    END
                END
                ELSE
                BEGIN
                    PRINT '    → NOT READY (needs ' + CAST(@RequiredApprovals - @ActualApprovals AS NVARCHAR(10)) + ' more approvals)';
                END

            END TRY
            BEGIN CATCH
                SET @ErrorMessage = ERROR_MESSAGE();
                PRINT '  ERROR for ' + @SourceTable + ' ID ' + @SourceID + ': ' + @ErrorMessage;
            END CATCH

            FETCH NEXT FROM item_cursor INTO @SourceID, @CurrentStageId;
        END

        CLOSE item_cursor;
        DEALLOCATE item_cursor;

        PRINT '  Source ' + @SourceTable + ': ' + 
              CAST(@SourceProcessed AS NVARCHAR(10)) + ' items processed, ' + 
              CAST(@SourceMoved AS NVARCHAR(10)) + ' moved to next stage';
        
        SET @TotalProcessed = @TotalProcessed + @SourceProcessed;

        FETCH NEXT FROM source_cursor INTO @SourceTable;
    END

    CLOSE source_cursor;
    DEALLOCATE source_cursor;

    -- ===================================================================
    -- PHASE 2: Create INITIAL tasks for items with history but no pending
    -- ===================================================================
    PRINT CHAR(13) + '--- PHASE 2: Creating Initial Tasks for Orphaned Items ---';
    
    DECLARE @InitialCreated INT = 0;
    
    -- Create a temp table to store items that need next stage tasks
    CREATE TABLE #ItemsNeedingTasks (
        Source NVARCHAR(255),
        SourceID NVARCHAR(100),
        CurrentStageId BIGINT,
        CurrentOrder INT,
        PermissionId BIGINT,
        NextStageId BIGINT,
        NextStageCount INT,
        NextPermissionId BIGINT,
        RowNum INT
    );
    
    -- Find items where the LATEST completed stage is complete but no next stage tasks exist
    INSERT INTO #ItemsNeedingTasks
    SELECT 
        comp.Source,
        comp.SourceID,
        comp.CurrentStageId,
        comp.CurrentOrder,
        comp.PermissionId,
        ns.Id as NextStageId,
        ns.[Count] as NextStageCount,
        ns.PermissionId as NextPermissionId,
        ROW_NUMBER() OVER (PARTITION BY comp.Source, comp.SourceID ORDER BY comp.CurrentOrder DESC) as RowNum
    FROM (
        -- Get completed stages for each item
        SELECT 
            h.Source,
            h.SourceID,
            s.Id as CurrentStageId,
            s.[Order] as CurrentOrder,
            s.PermissionId
        FROM (
            -- Get approval counts per stage
            SELECT 
                Source,
                SourceID,
                Stage,
                COUNT(DISTINCT CreatedBy) as ApprovalCount
            FROM t_WorkFlowHistoryTest
            WHERE IsApproved = 1
              AND DeletedOn IS NULL
            GROUP BY Source, SourceID, Stage
        ) h
        INNER JOIN t_WorkFlowStagesTest s ON h.Stage = CAST(s.Id AS NVARCHAR(50))
        WHERE h.ApprovalCount >= s.[Count]  -- Stage is complete
          AND s.DeletedOn IS NULL
    ) comp
    CROSS APPLY (
        -- FIX: Get next stage by ORDER only, not PermissionId
        SELECT TOP 1 ns.Id, ns.[Count], ns.PermissionId
        FROM t_WorkFlowStagesTest ns
        WHERE ns.[Order] = comp.CurrentOrder + 1  -- CHANGED: Removed PermissionId check
          AND ns.DeletedOn IS NULL
        ORDER BY ns.Id
    ) ns
    WHERE NOT EXISTS (
        -- No pending tasks for this item
        SELECT 1 
        FROM t_WorkFlowPendingTest p
        WHERE p.Source = comp.Source
          AND p.SourceID = comp.SourceID
          AND p.DeletedOn IS NULL
    )
    AND NOT EXISTS (
        -- No pending tasks for the next stage
        SELECT 1 
        FROM t_WorkFlowPendingTest p
        WHERE p.Source = comp.Source
          AND p.SourceID = comp.SourceID
          AND p.Stage = CAST(ns.Id AS NVARCHAR(50))
          AND p.DeletedOn IS NULL
    )
    -- CRITICAL FIX: Check that the next stage is NOT already complete
    AND NOT EXISTS (
        SELECT 1
        FROM (
            SELECT 
                Source,
                SourceID,
                Stage,
                COUNT(DISTINCT CreatedBy) as ApprovalCount
            FROM t_WorkFlowHistoryTest
            WHERE IsApproved = 1
              AND DeletedOn IS NULL
            GROUP BY Source, SourceID, Stage
        ) h2
        INNER JOIN t_WorkFlowStagesTest s2 ON h2.Stage = CAST(s2.Id AS NVARCHAR(50))
        WHERE h2.Source = comp.Source
          AND h2.SourceID = comp.SourceID
          AND s2.Id = ns.Id
          AND h2.ApprovalCount >= s2.[Count]  -- Next stage is already complete
    );
    
    -- Create next stage tasks only for the LATEST completed stage of each item
    INSERT INTO t_WorkFlowPendingTest (
        Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
    )
    SELECT 
        t.Source,
        t.SourceID,
        CAST(t.NextStageId AS NVARCHAR(50)),
        u.Id,
        @SystemUserId,
        @Now,
        @SystemUserId,
        @Now
    FROM #ItemsNeedingTasks t
    CROSS APPLY (
        SELECT TOP (t.NextStageCount) u.Id
        FROM t_Users u
        WHERE u.DeletedOn IS NULL
          AND u.Id IN (SELECT Id FROM f_getUserWithPermission(t.NextPermissionId))
          AND NOT EXISTS (
              SELECT 1 
              FROM t_WorkFlowHistoryTest h
              WHERE h.Source = t.Source
                AND h.SourceID = t.SourceID
                AND h.CreatedBy = u.Id
                AND h.IsApproved = 1
                AND h.DeletedOn IS NULL
          )
          AND NOT EXISTS (
              SELECT 1 
              FROM t_WorkFlowPendingTest p
              WHERE p.Source = t.Source
                AND p.SourceID = t.SourceID
                AND p.Stage = CAST(t.NextStageId AS NVARCHAR(50))
                AND p.UserId = u.Id
                AND p.DeletedOn IS NULL
          )
          AND u.Id != 1  -- Don't assign to system user
        ORDER BY u.Id
    ) u
    WHERE t.RowNum = 1  -- Only take the LATEST completed stage for each item
      AND t.NextStageId IS NOT NULL;  -- Only if next stage exists
    
    SET @InitialCreated = @@ROWCOUNT;
    SET @TotalCreated = @TotalCreated + @InitialCreated;
    
    DROP TABLE #ItemsNeedingTasks;
    
    PRINT 'Created ' + CAST(@InitialCreated AS NVARCHAR(10)) + ' initial tasks for orphaned items';

    -- ===================================================================
    -- PHASE 3: Clean up - Mark completed items as deleted
    -- ===================================================================
    PRINT CHAR(13) + '--- PHASE 3: Cleaning Up Completed Items ---';
    
    DECLARE @CleanedUp INT = 0;
    
    -- Mark as deleted any pending tasks where the stage is already complete
    UPDATE p
    SET p.DeletedOn = @Now,
        p.DeletedBy = @SystemUserId
    FROM t_WorkFlowPendingTest p
    INNER JOIN t_WorkFlowStagesTest s ON p.Stage = CAST(s.Id AS NVARCHAR(50))
    WHERE p.DeletedOn IS NULL
      AND EXISTS (
          SELECT 1
          FROM t_WorkFlowHistoryTest h
          WHERE h.Source = p.Source
            AND h.SourceID = p.SourceID
            AND h.Stage = p.Stage
            AND h.IsApproved = 1
            AND h.DeletedOn IS NULL
          GROUP BY h.Source, h.SourceID, h.Stage
          HAVING COUNT(DISTINCT h.CreatedBy) >= s.[Count]
      );
    
    SET @CleanedUp = @@ROWCOUNT;
    
    PRINT 'Cleaned up ' + CAST(@CleanedUp AS NVARCHAR(10)) + ' completed pending tasks';

    PRINT CHAR(13) + '=== PROCESSING COMPLETED ===';
    PRINT 'Total items processed: ' + CAST(@TotalProcessed AS NVARCHAR(10));
    PRINT 'Total items moved to next stage: ' + CAST(@TotalMoved AS NVARCHAR(10));
    PRINT 'Total new tasks created: ' + CAST(@TotalCreated AS NVARCHAR(10));
    PRINT 'Total cleaned up: ' + CAST(@CleanedUp AS NVARCHAR(10));
END;
GO

