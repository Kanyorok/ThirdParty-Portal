USE [BR_ERP]
GO
/****** Object:  StoredProcedure [dbo].[p_ProcessWorkflowStagesTest1]    Script Date: 31/12/2025 17:05:52 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

ALTER PROCEDURE [dbo].[p_ProcessWorkflowStagesTest1]
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON; -- Automatically rollback transaction on error

    DECLARE
        @SystemUserId BIGINT,
        @Now DATETIME = GETDATE(),
        @CurrentOrder INT,
        @PermissionId BIGINT,
        @StagePermissionId BIGINT,
        @StageId BIGINT,
        @Count INT,
        @WorkflowType NVARCHAR(100),
        @Source NVARCHAR(100),
        @SourceId NVARCHAR(100),
        @ApprovedStatusId BIGINT,
        @ActualApprovals INT,
        @RequiredApprovals INT,
        @NextStageId BIGINT,
        @Amount DECIMAL(20,4),
        @WorkflowLimit DECIMAL(18,2),
        @TransactionName NVARCHAR(32) = 'WorkflowProcessing',
        @ErrorMessage NVARCHAR(4000),
        @ErrorSeverity INT,
        @ErrorState INT,
        @ErrorLine INT;

    -- Resolve SystemUserId
    SELECT TOP 1 @SystemUserId = Id FROM t_Users WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

    -- =============================================
    -- WORKFLOW PROCESSING WITH TRANSACTIONS
    -- =============================================
    BEGIN TRY
        -- Get the Approved status
        SELECT @ApprovedStatusId = ID
        FROM t_CodeDetails
        WHERE Description IN ('Approved', 'Approval');

        IF @ApprovedStatusId IS NULL
        BEGIN
            RAISERROR('Approved status not found in t_CodeDetails.', 16, 1);
            RETURN;
        END

        -- Build list of latest approved stages per Source + SourceID
        ;WITH LatestApproved AS
        (
            SELECT
                p.Source,
                p.SourceID,
                CAST(p.Stage AS BIGINT) AS StageId,
                s.PermissionId,
                ROW_NUMBER() OVER (PARTITION BY p.Source, p.SourceID ORDER BY CAST(p.Stage AS BIGINT) DESC) AS rn
            FROM t_WorkFlowPending p
            INNER JOIN t_WorkFlowHistory h
                ON p.Source = h.Source
                AND p.SourceID = h.SourceID
                AND p.Stage = h.Stage
            INNER JOIN t_WorkFlowStages s
                ON s.Id = p.Stage
            WHERE h.StatusId = @ApprovedStatusId
                AND p.DeletedOn IS NOT NULL
                AND s.PermissionId IS NOT NULL
        )
        SELECT Source, SourceID, StageId, PermissionId
        INTO #ApprovalItems
        FROM LatestApproved
        WHERE rn = 1;

        -- Create clustered index for better cursor performance
        CREATE CLUSTERED INDEX IX_ApprovalItems ON #ApprovalItems(Source, SourceID);

        DECLARE approval_cursor CURSOR LOCAL FORWARD_ONLY STATIC
        FOR SELECT Source, SourceID, StageId, PermissionId FROM #ApprovalItems;

        OPEN approval_cursor;

        -- Start transaction for batch processing
        BEGIN TRANSACTION @TransactionName;

        FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;

        WHILE @@FETCH_STATUS = 0
        BEGIN
            BEGIN TRY
                -- If the permission from cursor is null, skip (extra safety)
                IF @PermissionId IS NULL
                BEGIN
                    PRINT 'Skipping record with NULL PermissionId';
                    FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;
                    CONTINUE;
                END

                -- Fetch metadata for current stage WITHOUT overwriting @PermissionId
                SELECT
                    @CurrentOrder = s.[Order],
                    @StagePermissionId = s.PermissionId
                FROM t_WorkFlowStages s
                WHERE s.Id = @StageId AND s.DeletedOn IS NULL;

                -- Optional safety check if @StagePermissionId is NULL
                IF @StagePermissionId IS NULL
                BEGIN
                    PRINT 'StagePermissionId is NULL for StageId=' + CAST(@StageId AS NVARCHAR(50));
                    FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;
                    CONTINUE;
                END

                -- Compare permission from cursor vs stage permission metadata if needed
                IF @PermissionId <> @StagePermissionId
                BEGIN
                    PRINT 'WARNING: Workflow stage permission doesnt match provided permission id. Skipping this record.';
                    FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;
                    CONTINUE;
                END

                -- Get WorkflowType and Count using stageId
                SELECT
                    @WorkflowType = wt.TypeID,
                    @Count = ISNULL(s.[Count], 0)
                FROM t_WorkFlowStages s
                JOIN t_WorkFlowTypes wt ON s.WorkFlowTypeId = wt.Id
                WHERE s.Id = @StageId;

                -- Count actual approvals
                SELECT @ActualApprovals = COUNT(DISTINCT h.CreatedBy)
                FROM t_WorkFlowHistory h
                WHERE h.Source = @Source
                    AND h.SourceID = @SourceId
                    AND h.Stage = CAST(@StageId AS NVARCHAR(50))
                    AND h.StatusId = @ApprovedStatusId
                    AND h.DeletedOn IS NULL;

                -- Determine required approvals based on WorkflowType
                IF @WorkflowType = 'ALL'
                BEGIN
                    SELECT @RequiredApprovals = COUNT(*)
                    FROM t_Users u
                    WHERE u.DeletedOn IS NULL
                        AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@PermissionId));
                END
                ELSE IF @WorkflowType = 'CNT'
                BEGIN
                    SET @RequiredApprovals = @Count;
                END
                ELSE IF @WorkflowType = 'MAJ'
                BEGIN
                    SELECT @RequiredApprovals = CEILING(COUNT(*) * 1.0 / 2)
                    FROM t_Users u
                    WHERE u.DeletedOn IS NULL
                        AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@PermissionId));
                END
                ELSE IF @WorkflowType = 'AMT'
                BEGIN
                    SET @RequiredApprovals = @Count;
                END
                ELSE
                BEGIN
                    SET @RequiredApprovals = 0;
                END

                -- If enough approvals, move to next stage
                IF @RequiredApprovals > 0 AND @ActualApprovals >= @RequiredApprovals
                BEGIN
                    -- Find next stage
                    SELECT TOP 1 @NextStageId = s2.Id
                    FROM t_WorkFlowStages s2
                    WHERE s2.[Order] = @CurrentOrder + 1
                        AND s2.DeletedOn IS NULL;

                    -- If next stage does not exist, end here
                    IF @NextStageId IS NULL
                    BEGIN
                        PRINT 'Workflow ends at current stage for Source: ' + @Source + ', SourceID: ' + @SourceId;
                        FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;
                        CONTINUE;
                    END

                    -- Get next stage metadata
                    SELECT TOP 1
                        @StagePermissionId = s2.PermissionId,
                        @WorkflowType = wt2.TypeID,
                        @Count = ISNULL(s2.[Count], 0)
                    FROM t_WorkFlowStages s2
                    JOIN t_WorkFlowTypes wt2 ON s2.WorkFlowTypeId = wt2.Id
                    WHERE s2.Id = @NextStageId;

                    -- If AMT type, check amount vs limit before inserting
                    IF @WorkflowType = 'AMT'
                    BEGIN
                        BEGIN TRY
                            -- Get Amount from source table dynamically
                            DECLARE @sql NVARCHAR(MAX) = N'SELECT @AmountOut = ISNULL(Amount, 0) FROM '
                                + QUOTENAME(@Source) + ' WHERE Id = @SourceID';

                            EXEC sp_executesql @sql,
                                N'@SourceID VARCHAR(100), @AmountOut DECIMAL(18,2) OUTPUT',
                                @SourceID = @SourceId,
                                @AmountOut = @Amount OUTPUT;

                            SELECT TOP 1 @WorkflowLimit = MaxAmount
                            FROM t_WorkFlowLimitsTest
                            WHERE Source = @Source
                                AND PermissionId = @StagePermissionId
                                AND DeletedOn IS NULL;

                            IF @WorkflowLimit IS NULL
                            BEGIN
                                PRINT 'Workflow limit is null. Skipping pending insert for Source: ' + @Source + ', SourceID: ' + @SourceId;
                                FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;
                                CONTINUE;
                            END

                            IF @Amount > @WorkflowLimit
                            BEGIN
                                PRINT 'Amount exceeds workflow limit. Skipping pending insert for Source: ' + @Source + ', SourceID: ' + @SourceId;
                                FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;
                                CONTINUE;
                            END
                        END TRY
                        BEGIN CATCH
                            PRINT 'Error checking amount for AMT workflow: ' + ERROR_MESSAGE();
                            FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;
                            CONTINUE;
                        END CATCH
                    END

                    -- Insert pending approvals for the next stage
                    INSERT INTO t_WorkFlowPending (
                        Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn
                    )
                    SELECT
                        @Source,
                        @SourceId,
                        CAST(@NextStageId AS NVARCHAR(50)),
                        u.Id,
                        @SystemUserId,
                        @Now,
                        @SystemUserId,
                        @Now
                    FROM t_Users u
                    WHERE u.DeletedOn IS NULL
                        AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@StagePermissionId))
                        AND NOT EXISTS (
                            SELECT 1 FROM t_WorkFlowPending p
                            WHERE p.Source = @Source
                                AND p.SourceID = @SourceId
                                AND p.Stage = CAST(@NextStageId AS NVARCHAR(50))
                                AND p.UserId = u.Id
                                AND p.DeletedOn IS NULL
                        );

                    PRINT 'Successfully moved to next stage: Source: ' + @Source + ', SourceID: ' + @SourceId +
                            ', From Stage: ' + CAST(@StageId AS NVARCHAR(50)) + ', To Stage: ' + CAST(@NextStageId AS NVARCHAR(50));
                END
            END TRY
            BEGIN CATCH
                -- Log individual record error but continue processing other records
                SELECT
                    @ErrorMessage = ERROR_MESSAGE(),
                    @ErrorLine = ERROR_LINE();

                PRINT 'Error processing record Source: ' + @Source + ', SourceID: ' + @SourceId +
                      ' at line ' + CAST(@ErrorLine AS NVARCHAR(10)) + ': ' + @ErrorMessage;

                -- Continue with next record
            END CATCH

            FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;
        END

        -- Commit the transaction
        COMMIT TRANSACTION @TransactionName;

        PRINT 'Transaction committed successfully.';

        CLOSE approval_cursor;
        DEALLOCATE approval_cursor;

        IF OBJECT_ID('tempdb..#ApprovalItems') IS NOT NULL
            DROP TABLE #ApprovalItems;

        PRINT 'Workflow stage processing completed successfully.';

    END TRY
    BEGIN CATCH
        -- Handle main procedure error
        SELECT
            @ErrorMessage = ERROR_MESSAGE(),
            @ErrorSeverity = ERROR_SEVERITY(),
            @ErrorState = ERROR_STATE(),
            @ErrorLine = ERROR_LINE();

        -- Rollback transaction if still active
        IF @@TRANCOUNT > 0
        BEGIN
            ROLLBACK TRANSACTION;
            PRINT 'Transaction rolled back due to error.';
        END

        -- Cleanup resources
        IF CURSOR_STATUS('global', 'approval_cursor') >= 0
        BEGIN
            CLOSE approval_cursor;
            DEALLOCATE approval_cursor;
        END

        IF OBJECT_ID('tempdb..#ApprovalItems') IS NOT NULL
            DROP TABLE #ApprovalItems;

        -- Log error and re-throw
        PRINT 'Procedure failed at line ' + CAST(@ErrorLine AS NVARCHAR(10)) + ': ' + @ErrorMessage;

        RAISERROR (@ErrorMessage, @ErrorSeverity, @ErrorState);
        RETURN;
    END CATCH

    -- =============================================
    -- PROCEDURE COMPLETE
    -- =============================================
    PRINT 'Procedure p_ProcessWorkflowStagesTest completed.';
END;
