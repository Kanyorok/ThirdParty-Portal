CREATE OR ALTER PROCEDURE [dbo].[p_ProcessWorkflowStages]
AS
BEGIN

    SET NOCOUNT ON;

    DECLARE
        @SystemUserId BIGINT,

        @Now DATETIME = GETDATE(),

        @CurrentOrder INT,

        @PermissionId BIGINT,

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

    -- Resolve SystemUserId dynamically (e.g., user with username = 'system')
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

            ;
        WITH LatestApproved AS
                 (SELECT p.Source,

                         p.SourceID,

                         CAST(p.Stage AS BIGINT)                                                                     AS StageId,

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

                    AND p.DeletedOn IS NOT NULL)

        SELECT Source, SourceID, StageId, PermissionId

        INTO #ApprovalItems

        FROM LatestApproved

        WHERE rn = 1;

        DECLARE approval_cursor CURSOR FOR
            SELECT Source, SourceID, StageId, PermissionId FROM #ApprovalItems;

        OPEN approval_cursor;

        FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;


        if @PermissionId <> @WorkflowStagePermission
            BEGIN
                RAISERROR ('Workflow stage permission doesnt match provided permission id.', 16, 1);
                RETURN;

            END

        if @PermissionId is null
            BEGIN
                RAISERROR ('PermissionId is NULL', 16, 1);
                RETURN;
            END


        WHILE @@FETCH_STATUS = 0
            BEGIN

                -- Fetch metadata for current stage

                SELECT @CurrentOrder = s.[Order],

                       @PermissionId = s.PermissionId

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

                -- Determine required approvals based on WorkflowType

                IF @WorkflowType = 'ALL'
                    BEGIN

                        SELECT @RequiredApprovals = COUNT(*)

                        FROM t_Users u

                        WHERE u.DeletedOn IS NULL

                          AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@PermissionId));

                    END

                ELSE
                    IF @WorkflowType = 'CNT'
                        BEGIN

                            SET @RequiredApprovals = @Count;

                        END

                    ELSE
                        IF @WorkflowType = 'MAJ'
                            BEGIN

                                SELECT @RequiredApprovals = CEILING(COUNT(*) * 1.0 / 2)

                                FROM t_Users u

                                WHERE u.DeletedOn IS NULL

                                  AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@PermissionId));

                            END

                        ELSE
                            IF @WorkflowType = 'AMT'
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

                        --IF @NextStageId IS NOT NULL

                        --if next stage doesnot exists, end here
                        IF @NextStageId IS NULL OR @NextStageId = ''
                            BEGIN
                                RETURN
                                --	RAISERROR ('No Next Stage To Approve',16,1 )
                                --	FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;
                                --CONTINUE;
                            END
                        BEGIN
                            -- Get next stage metadata

                            SELECT TOP 1 @PermissionId = s2.PermissionId,

                                         @WorkflowType = wt2.TypeID,

                                         @Count = ISNULL(s2.[Count], 0)

                            FROM t_WorkFlowStages s2

                                     JOIN t_WorkFlowTypes wt2 ON s2.WorkFlowTypeId = wt2.Id

                            WHERE s2.Id = @NextStageId;

                            -- If AMT type, check amount vs limit before inserting

                            IF @WorkflowType = 'AMT'
                                BEGIN

                                    -- Get Amount from source table dynamically

                                    DECLARE @sql NVARCHAR(MAX) = N'SELECT @AmountOut = ISNULL(Amount, 0)

                            FROM ' + QUOTENAME(@Source) + '

                            WHERE Id = @SourceID';

                                    EXEC sp_executesql @sql,
                                         N'@SourceID VARCHAR(100), @AmountOut DECIMAL(18,2) OUTPUT',
                                         @SourceID = @SourceId,
                                         @AmountOut = @Amount OUTPUT;

                                    SELECT TOP 1 @WorkflowLimit = MaxAmount

                                    FROM t_WorkFlowLimits

                                    WHERE Source = @Source

                                      AND PermissionId = @PermissionId

                                      AND DeletedOn IS NULL;

                                    IF @WorkflowLimit IS NULL
                                        BEGIN

                                            PRINT 'Workflow limit is null. Skipping pending insert.';

                                            FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;

                                            CONTINUE;

                                        END

                                    IF @Amount > @WorkflowLimit
                                        BEGIN

                                            PRINT 'Amount exceeds workflow limit. Skipping pending insert.';

                                            FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;

                                            CONTINUE;

                                        END

                                END

                            -- Insert pending approvals for the next stage

                            INSERT INTO t_WorkFlowPending (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn,
                                                           ModifiedBy, ModifiedOn)

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

                              AND u.Id IN (SELECT Id FROM f_getUserWithPermission(@PermissionId))

                              AND NOT EXISTS (SELECT 1
                                              FROM t_WorkFlowPending p

                                              WHERE p.Source = @Source

                                                AND p.SourceID = @SourceId

                                                AND p.Stage = CAST(@NextStageId AS NVARCHAR(50))

                                                AND p.UserId = u.Id

                                                AND p.DeletedOn IS NULL);

                        END

                    END

                FETCH NEXT FROM approval_cursor INTO @Source, @SourceId, @StageId, @PermissionId;

            END

        CLOSE approval_cursor;

        DEALLOCATE approval_cursor;

        DROP TABLE #ApprovalItems;

    END TRY
    BEGIN CATCH

        DECLARE
            @ErrMsg NVARCHAR(4000),

            @ErrSev INT,

            @ErrState INT;

        SELECT @ErrMsg = ERROR_MESSAGE(),

               @ErrSev = ERROR_SEVERITY(),

               @ErrState = ERROR_STATE();

        RAISERROR (@ErrMsg, @ErrSev, @ErrState);

    END CATCH

END;
