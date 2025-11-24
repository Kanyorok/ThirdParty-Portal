CREATE or alter PROCEDURE [dbo].[p_ProcessWorkflowPending]
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE
        @Now DATETIME = GETDATE(),
        @SystemUserId BIGINT,
        @SubmittedStatusId BIGINT,
        @ApprovedStatusId BIGINT,
        @RejectedStatusId BIGINT;

    -- Resolve SystemUserId dynamically (e.g., user with username = 'system')
    SELECT TOP 1 @SystemUserId = Id FROM t_Users WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

    IF @SystemUserId IS NULL
        BEGIN
            RAISERROR ('System user not found.', 16, 1);
            RETURN;
        END

    -- Get status IDs dynamically
    SELECT @SubmittedStatusId = id FROM t_codeDetails WHERE [Description] = 'Submitted for Approval';
    SELECT @ApprovedStatusId = id FROM t_codeDetails WHERE [Description] = 'Approved';
    SELECT @RejectedStatusId = id FROM t_codeDetails WHERE [Description] = 'Rejected';

    IF @SubmittedStatusId IS NULL OR @ApprovedStatusId IS NULL OR @RejectedStatusId IS NULL
        BEGIN
            RAISERROR ('Required workflow status IDs are missing.', 16, 1);
            RETURN;
        END

    DECLARE
        @Source NVARCHAR(255),
        @SourceID NVARCHAR(100),
        @StageId BIGINT,
        @PermissionId BIGINT,
        @WorkflowStagePermission BIGINT,
        @WorkflowType NVARCHAR(50),
        @Count INT;

    DECLARE history_cursor CURSOR FOR
        SELECT h.Source,
               h.SourceID,
               s.Id,
               s.PermissionId,
               wt.TypeID,
               ISNULL(s.[Count], 0)
        FROM t_WorkFlowHistory h
                 INNER JOIN t_WorkFlows wf ON h.Source = wf.Source AND wf.DeletedOn IS NULL
                 INNER JOIN t_WorkFlowStages s ON wf.Id = s.WorkFlowId AND s.[Order] = 1 AND s.DeletedOn IS NULL
                 INNER JOIN t_WorkFlowTypes wt ON s.WorkFlowTypeId = wt.Id
        WHERE h.StatusId = @SubmittedStatusId
          AND h.DeletedOn IS NULL;

    OPEN history_cursor;
    FETCH NEXT FROM history_cursor INTO @Source, @SourceID, @StageId, @PermissionId, @WorkflowType, @Count;
    IF @PermissionId <> @WorkflowStagePermission
        BEGIN
            RAISERROR ('Workflow stage permission doesnt match provided permission id.', 16, 1);
            RETURN;
        END
    IF @PermissionId IS NULL
        BEGIN
            RAISERROR ('PermissionId is NULL.', 16, 1);
            RETURN;
        END

    WHILE @@FETCH_STATUS = 0
        BEGIN
            DECLARE @InsertedPending TABLE
                                     (
                                         Source   NVARCHAR(255),
                                         SourceID NVARCHAR(100),
                                         Stage    NVARCHAR(50),
                                         UserId   BIGINT
                                     );

            DECLARE @InsertCount INT;

            IF @WorkflowType = 'ALL'
                BEGIN
                    INSERT INTO t_WorkFlowPending (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn, ModifiedBy,
                                                   ModifiedOn)
                    OUTPUT INSERTED.Source,
                           INSERTED.SourceID,
                           INSERTED.Stage,
                           INSERTED.UserId
                        INTO @InsertedPending
                    SELECT @Source,
                           @SourceID,
                           CAST(@StageId AS NVARCHAR(50)),
                           u.Id,
                           @SystemUserId,
                           @Now,
                           @SystemUserId,
                           @Now
                    FROM t_Users u
                    WHERE u.Id IN (SELECT id FROM f_getUserWithPermission(@PermissionId))
                      AND NOT EXISTS (SELECT 1
                                      FROM t_WorkFlowPending p
                                      WHERE p.Source = @Source
                                        AND p.SourceID = @SourceID
                                        AND p.Stage = CAST(@StageId AS NVARCHAR(50))
                                        AND p.UserId = u.Id)
                      AND NOT EXISTS (SELECT 1
                                      FROM t_WorkFlowHistory h2
                                      WHERE h2.Source = @Source
                                        AND h2.SourceID = @SourceID
                                        AND h2.CreatedBy = u.Id
                                        AND h2.StatusId IN (@ApprovedStatusId, @RejectedStatusId));
                END
            ELSE
                IF @WorkflowType = 'CNT'
                    BEGIN
                        INSERT INTO t_WorkFlowPending (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn,
                                                       ModifiedBy, ModifiedOn)
                        OUTPUT INSERTED.Source,
                               INSERTED.SourceID,
                               INSERTED.Stage,
                               INSERTED.UserId
                            INTO @InsertedPending
                        SELECT TOP (@Count) @Source,
                                            @SourceID,
                                            CAST(@StageId AS NVARCHAR(50)),
                                            u.Id,
                                            @SystemUserId,
                                            @Now,
                                            @SystemUserId,
                                            @Now
                        FROM t_Users u
                        WHERE u.Id IN (SELECT id FROM f_getUserWithPermission(@PermissionId))
                          AND NOT EXISTS (SELECT 1
                                          FROM t_WorkFlowPending p
                                          WHERE p.Source = @Source
                                            AND p.SourceID = @SourceID
                                            AND p.Stage = CAST(@StageId AS NVARCHAR(50))
                                            AND p.UserId = u.Id)
                          AND NOT EXISTS (SELECT 1
                                          FROM t_WorkFlowHistory h2
                                          WHERE h2.Source = @Source
                                            AND h2.SourceID = @SourceID
                                            AND h2.CreatedBy = u.Id
                                            AND h2.StatusId IN (@ApprovedStatusId, @RejectedStatusId))
                        ORDER BY u.Id;
                    END
                ELSE
                    IF @WorkflowType = 'MAJ'
                        BEGIN
                            DECLARE @TotalCount INT;
                            SELECT @TotalCount = COUNT(*)
                            FROM t_Users u
                            WHERE u.Id IN (SELECT id FROM f_getUserWithPermission(@PermissionId));

                            SET @InsertCount = (@TotalCount / 2) + 1;

                            INSERT INTO t_WorkFlowPending (Source, SourceID, Stage, UserId, CreatedBy, CreatedOn,
                                                           ModifiedBy, ModifiedOn)
                            OUTPUT INSERTED.Source,
                                   INSERTED.SourceID,
                                   INSERTED.Stage,
                                   INSERTED.UserId
                                INTO @InsertedPending
                            SELECT TOP (@InsertCount) @Source,
                                                      @SourceID,
                                                      CAST(@StageId AS NVARCHAR(50)),
                                                      u.Id,
                                                      @SystemUserId,
                                                      @Now,
                                                      @SystemUserId,
                                                      @Now
                            FROM t_Users u
                            WHERE u.Id IN (SELECT id FROM f_getUserWithPermission(@PermissionId))
                              AND NOT EXISTS (SELECT 1
                                              FROM t_WorkFlowPending p
                                              WHERE p.Source = @Source
                                                AND p.SourceID = @SourceID
                                                AND p.Stage = CAST(@StageId AS NVARCHAR(50))
                                                AND p.UserId = u.Id)
                              AND NOT EXISTS (SELECT 1
                                              FROM t_WorkFlowHistory h2
                                              WHERE h2.Source = @Source
                                                AND h2.SourceID = @SourceID
                                                AND h2.CreatedBy = u.Id
                                                AND h2.StatusId IN (@ApprovedStatusId, @RejectedStatusId))
                            ORDER BY u.Id;
                        END
                    ELSE
                        IF @WorkflowType = 'AMT'
                            BEGIN
                                DECLARE @Amount DECIMAL(18, 2) = 0, @WorkflowLimit DECIMAL(18, 2);

                                DECLARE @sql NVARCHAR(MAX) = N'
                SELECT @AmountOut = ISNULL(Amount, 0)
                FROM ' + QUOTENAME(@Source) + '
                WHERE Id = @SourceID';

                                EXEC sp_executesql @sql,
                                     N'@SourceID VARCHAR(100), @AmountOut DECIMAL(18,2) OUTPUT',
                                     @SourceID = @SourceID,
                                     @AmountOut = @Amount OUTPUT;

                                SELECT TOP 1 @WorkflowLimit = MaxAmount
                                FROM t_WorkFlowLimits
                                WHERE Source = @Source
                                  AND PermissionId = @PermissionId;

                                IF @WorkflowLimit IS NOT NULL AND @Amount > @WorkflowLimit
                                    BEGIN
                                        PRINT 'Amount exceeds workflow limit. Skipping insert.';
                                        FETCH NEXT FROM history_cursor INTO @Source, @SourceID, @StageId, @PermissionId, @WorkflowType, @Count;
                                        CONTINUE;
                                    END

                                SET @InsertCount = FLOOR(@Amount);

                                IF @InsertCount > 0
                                    BEGIN
                                        INSERT INTO t_WorkFlowPending (Source, SourceID, Stage, UserId, CreatedBy,
                                                                       CreatedOn, ModifiedBy, ModifiedOn)
                                        OUTPUT INSERTED.Source,
                                               INSERTED.SourceID,
                                               INSERTED.Stage,
                                               INSERTED.UserId
                                            INTO @InsertedPending
                                        SELECT TOP (@InsertCount) @Source,
                                                                  @SourceID,
                                                                  CAST(@StageId AS NVARCHAR(50)),
                                                                  u.Id,
                                                                  @SystemUserId,
                                                                  @Now,
                                                                  @SystemUserId,
                                                                  @Now
                                        FROM t_Users u
                                        WHERE u.Id IN (SELECT id FROM f_getUserWithPermission(@PermissionId))
                                          AND NOT EXISTS (SELECT 1
                                                          FROM t_WorkFlowPending p
                                                          WHERE p.Source = @Source
                                                            AND p.SourceID = @SourceID
                                                            AND p.Stage = CAST(@StageId AS NVARCHAR(50))
                                                            AND p.UserId = u.Id)
                                          AND NOT EXISTS (SELECT 1
                                                          FROM t_WorkFlowHistory h2
                                                          WHERE h2.Source = @Source
                                                            AND h2.SourceID = @SourceID
                                                            AND h2.CreatedBy = u.Id
                                                            AND h2.StatusId IN (@ApprovedStatusId, @RejectedStatusId))
                                        ORDER BY u.Id;
                                    END
                            END

            -- Send notification emails
            DECLARE
                @PendingSource NVARCHAR(255),
                @PendingSourceID NVARCHAR(100),
                @PendingStage NVARCHAR(50),
                @PendingUserId BIGINT;

            DECLARE email_cursor CURSOR FOR
                SELECT Source, SourceID, Stage, UserId FROM @InsertedPending;

            OPEN email_cursor;
            FETCH NEXT FROM email_cursor INTO @PendingSource, @PendingSourceID, @PendingStage, @PendingUserId;

            WHILE @@FETCH_STATUS = 0
                BEGIN
                    DECLARE @UserEmail NVARCHAR(255);
                    SELECT @UserEmail = Email FROM t_Users WHERE Id = @PendingUserId;

                    IF @UserEmail IS NOT NULL
                        BEGIN
                            DECLARE @EmailMessage NVARCHAR(MAX) =
                                'You have been assigned to a new workflow task for Source: ' + @PendingSource +
                                ', ID: ' + @PendingSourceID;

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
            -- Soft delete processed workflow history
            UPDATE t_WorkFlowHistory
            SET DeletedOn  = @Now,
                DeletedBy  = @SystemUserId,
                ModifiedOn = @Now,
                ModifiedBy = @SystemUserId
            WHERE Source = @Source
              AND SourceID = @SourceID
              AND Stage = CAST(@StageId AS NVARCHAR(200))
              AND StatusId = @SubmittedStatusId
              AND DeletedOn IS NULL;

            FETCH NEXT FROM history_cursor INTO @Source, @SourceID, @StageId, @PermissionId, @WorkflowType, @Count;
        END

    CLOSE history_cursor;
    DEALLOCATE history_cursor;
END;
