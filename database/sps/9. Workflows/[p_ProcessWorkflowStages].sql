/****** Object:  StoredProcedure [dbo].[p_ProcessWorkflowStages]    Script Date: 29/12/2025 12:54:22 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER   PROCEDURE [dbo].[p_ProcessWorkflowStages]
 @PermissionId BIGINT = NULL
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE
        @SystemUserId BIGINT,
        @Now DATETIME = GETDATE(),
        @CurrentOrder INT,
        @StageId BIGINT,
        @Count INT,
        @WorkflowType NVARCHAR(100),
        @Source NVARCHAR(100),
        @SourceId NVARCHAR(100),
        @ApprovedStatusId BIGINT,
        @ActualApprovals INT,
        @RequiredApprovals INT,
        @NextStageId BIGINT,
        @Amount DECIMAL(20, 4),
        @WorkflowStagePermission BIGINT,
        @WorkflowLimit DECIMAL(18, 2);

    -- Resolve SystemUserId
    SELECT TOP 1 @SystemUserId = Id FROM t_Users WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

    BEGIN TRY
        -- Get the Approved status
        SELECT @ApprovedStatusId = ID
        FROM t_CodeDetails
        WHERE Description IN ('Approved', 'Approval');

        IF @ApprovedStatusId IS NULL
        BEGIN
            RAISERROR ('Approved status not found in t_CodeDetails.', 16, 1);
            RETURN;
        END

        -- Build list of latest approved stages per Source + SourceID
        ;WITH LatestApproved AS
        (
            SELECT p.Source,
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
                ON s.Id = CAST(p.Stage AS BIGINT)
            WHERE h.StatusId = @ApprovedStatusId
              AND p.DeletedOn IS NULL
        )
        SELECT Source, SourceID, StageId, PermissionId
        INTO #ApprovalItems
        FROM LatestApproved
        WHERE rn = 1;

        -- ✅ CHECK: If no items to process, exit gracefully
        IF NOT EXISTS (SELECT 1 FROM #ApprovalItems)
        BEGIN
            DROP TABLE #ApprovalItems;
            RETURN; -- Nothing to process, exit successfully
        END

        DECLARE approval_cursor CURSOR FOR
            SELECT Source, SourceID, StageId, PermissionId FROM #ApprovalItems;

        OPEN approval_cursor;
        FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @WorkflowStagePermission;

        WHILE @@FETCH_STATUS = 0
        BEGIN
            -- ✅ MOVED: Check permission match INSIDE the loop for each record
            IF @PermissionId IS NOT NULL AND @PermissionId <> @WorkflowStagePermission
            BEGIN
                -- Skip this item if permission doesn't match
                FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @WorkflowStagePermission;
                CONTINUE;
            END

            -- ✅ CHECK: Validate PermissionId is not NULL for this specific item
            IF @WorkflowStagePermission IS NULL
            BEGIN
                -- Log or handle this specific item with NULL permission
                -- For now, skip it and continue with next item
                FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @WorkflowStagePermission;
                CONTINUE;
            END

            -- Fetch metadata for current stage
            SELECT @CurrentOrder = s.[Order],
                   @WorkflowStagePermission = s.PermissionId
            FROM t_WorkFlowStages s
            WHERE s.Id = @StageId
              AND s.DeletedOn IS NULL;

            SELECT @WorkflowType = wt.TypeID,
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

            -- Determine required approvals
            IF @WorkflowType = 'ALL'
            BEGIN
                SELECT @RequiredApprovals = COUNT(*)
                FROM t_Users u
                WHERE u.DeletedOn IS NULL
                  AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@WorkflowStagePermission));
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
                  AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@WorkflowStagePermission));
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

                IF @NextStageId IS NULL OR @NextStageId = ''
                BEGIN
                    -- No next stage, workflow complete
                    FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @WorkflowStagePermission;
                    CONTINUE;
                END

                -- Get next stage metadata
                SELECT TOP 1 @WorkflowStagePermission = s2.PermissionId,
                             @WorkflowType = wt2.TypeID,
                             @Count = ISNULL(s2.[Count], 0)
                FROM t_WorkFlowStages s2
                JOIN t_WorkFlowTypes wt2 ON s2.WorkFlowTypeId = wt2.Id
                WHERE s2.Id = @NextStageId;

                -- Insert pending approvals for the next stage
                INSERT INTO t_WorkFlowPending (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy, ModifiedOn)
                SELECT @Source,
                       @SourceId,
                       CAST(@NextStageId AS NVARCHAR(50)),
                       u.Id,
                       @SystemUserId,
                       @Now,
                       @SystemUserId,
                       @Now
                FROM t_Users u
                WHERE u.DeletedOn IS NULL
                  AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@WorkflowStagePermission))
                  AND NOT EXISTS (SELECT 1
                                  FROM t_WorkFlowPending p
                                  WHERE p.Source = @Source
                                    AND p.SourceID = @SourceId
                                    AND p.Stage = CAST(@NextStageId AS NVARCHAR(50))
                                    AND p.UserId = u.Id
                                    AND p.DeletedOn IS NULL);
            END

            FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @WorkflowStagePermission;
        END

        CLOSE approval_cursor;
        DEALLOCATE approval_cursor;
        DROP TABLE #ApprovalItems;

    END TRY
    BEGIN CATCH
        DECLARE @ErrMsg NVARCHAR(4000), @ErrSev INT, @ErrState INT;
        SELECT @ErrMsg = ERROR_MESSAGE(), @ErrSev = ERROR_SEVERITY(), @ErrState = ERROR_STATE();
        RAISERROR (@ErrMsg, @ErrSev, @ErrState);
    END CATCH
END;
