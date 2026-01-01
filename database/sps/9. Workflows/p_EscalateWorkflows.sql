USE [BR_ERP]
GO

ALTER PROCEDURE [dbo].[p_EscalateWorkflows]
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON; -- Auto rollback on error

    DECLARE @StartTime DATETIME = GETDATE();
    DECLARE @ExecutedByUserId BIGINT;
    DECLARE @ItemsEscalated INT = 0;
    DECLARE @FirstEscalations INT = 0;
    DECLARE @ReEscalations INT = 0;

    -- Variables for email notifications
    DECLARE @SupervisorEmail NVARCHAR(255);
    DECLARE @SupervisorId BIGINT;
    DECLARE @EmailMessage NVARCHAR(MAX);
    DECLARE @EmailSubject NVARCHAR(255);
    DECLARE @CurrentPendingId BIGINT;
    DECLARE @CurrentStageName NVARCHAR(100);
    DECLARE @CurrentHoursOverdue DECIMAL(10,2);
    DECLARE @CurrentUserId BIGINT;
    DECLARE @AssignedUserName NVARCHAR(255);
    DECLARE @OverdueDays DECIMAL(10,2);
    DECLARE @IsReEscalation BIT; -- Added this variable

    -- Cursor for processing notifications
    DECLARE escalation_cursor CURSOR LOCAL FAST_FORWARD FOR
        SELECT
            ei.PendingId,
            ei.SupervisorId,
            ei.UserId,
            ei.StageName,
            ei.HoursOverdue,
            ei.IsReEscalation
        FROM #EscalationItems ei
        WHERE ei.SupervisorId IS NOT NULL;

    BEGIN TRY
        -- Get System User ID for execution
        SELECT TOP 1 @ExecutedByUserId = Id
        FROM t_Users WITH (NOLOCK)
        WHERE UserID = 'ERPSYS' AND DeletedOn IS NULL;

        -- If no system user found, use default (0) or throw error
        IF @ExecutedByUserId IS NULL
            SET @ExecutedByUserId = 0;

        BEGIN TRANSACTION;

        -- Create temp table to store escalation items
        CREATE TABLE #EscalationItems (
            PendingId BIGINT PRIMARY KEY,
            StageId BIGINT,
            UserId BIGINT,
            StageName NVARCHAR(100),
            EscalationLimit INT,
            IsReEscalation BIT,
            HoursOverdue DECIMAL(10,2),
            SupervisorId BIGINT
        );

        -- Find all items that need escalation
        INSERT INTO #EscalationItems (
            PendingId, StageId, UserId, StageName, EscalationLimit, IsReEscalation, HoursOverdue, SupervisorId
        )
        SELECT
            wp.Id AS PendingId,
            wp.Stage AS StageId,
            wp.UserId,
            ws.StageName,
            ws.EscalationLimit,
            CASE WHEN wp.EscalatedOn IS NOT NULL THEN 1 ELSE 0 END AS IsReEscalation,
            DATEDIFF(HOUR, COALESCE(wp.EscalatedOn, wp.CreatedOn), GETDATE()) - (ws.EscalationLimit * 24) AS HoursOverdue,
            dbo.f_getDepartmentHeadID(wp.UserId) AS SupervisorId
        FROM dbo.t_WorkFlowPendingTest wp WITH (NOLOCK)
        INNER JOIN dbo.t_WorkFlowStagesTest ws WITH (NOLOCK) ON wp.Stage = ws.Id
        WHERE wp.DeletedOn IS NULL
            AND ws.DeletedOn IS NULL
            AND ws.EscalationLimit IS NOT NULL
            AND DATEDIFF(HOUR, COALESCE(wp.EscalatedOn, wp.CreatedOn), GETDATE()) > (ws.EscalationLimit * 24);

        -- Get counts
        SELECT
            @ItemsEscalated = COUNT(*),
            @FirstEscalations = SUM(CASE WHEN IsReEscalation = 0 THEN 1 ELSE 0 END),
            @ReEscalations = SUM(CASE WHEN IsReEscalation = 1 THEN 1 ELSE 0 END)
        FROM #EscalationItems;

        -- If no items need escalation, return early
        IF @ItemsEscalated = 0
        BEGIN
            DROP TABLE #EscalationItems;
            COMMIT TRANSACTION;

            SELECT
                'SUCCESS' AS Status,
                'No items required escalation' AS Message,
                0 AS ItemsEscalated,
                0 AS FirstEscalations,
                0 AS ReEscalations,
                @StartTime AS StartedAt,
                GETDATE() AS CompletedAt,
                DATEDIFF(SECOND, @StartTime, GETDATE()) AS DurationSeconds;
            RETURN;
        END

        -- Insert into escalation history table
        INSERT INTO dbo.t_WorkFlowEscalation (
            WorkFlowStageId,
            UserId,
            SupervisorId,
            Notes,
            CreatedBy,
            CreatedOn,
            ModifiedBy,
            ModifiedOn
        )
        SELECT
            ei.StageId,
            ei.UserId,
            COALESCE(ei.SupervisorId, @ExecutedByUserId),
            CONCAT(
                'Automated Workflow Escalation - ',
                CASE WHEN ei.IsReEscalation = 0 THEN 'First Escalation' ELSE 'Re-escalation' END,
                ' | Stage: ', ei.StageName,
                ' | Overdue by: ', CAST(ROUND(ei.HoursOverdue, 1) AS NVARCHAR(20)), ' hours',
                ' | Limit: ', ei.EscalationLimit, ' days',
                ' | Executed: ', CONVERT(VARCHAR, GETDATE(), 120)
            ),
            @ExecutedByUserId,
            GETDATE(),
            @ExecutedByUserId,
            GETDATE()
        FROM #EscalationItems ei;

        -- Update pending items with new escalation timestamp
        UPDATE wp
        SET
            EscalatedOn = GETDATE(),
            ModifiedBy = @ExecutedByUserId,
            ModifiedOn = GETDATE()
        FROM dbo.t_WorkFlowPendingTest wp
        INNER JOIN #EscalationItems ei ON wp.Id = ei.PendingId;

        -- Commit transaction (keep emails outside transaction to avoid rollback on email failures)
        COMMIT TRANSACTION;

        -- SEND EMAIL NOTIFICATIONS TO SUPERVISORS
        OPEN escalation_cursor;
        FETCH NEXT FROM escalation_cursor INTO
            @CurrentPendingId, @SupervisorId, @CurrentUserId, @CurrentStageName,
            @CurrentHoursOverdue, @IsReEscalation; -- Changed to @IsReEscalation

        WHILE @@FETCH_STATUS = 0
        BEGIN
            -- Get supervisor email
            SELECT @SupervisorEmail = Email
            FROM t_Users WITH (NOLOCK)
            WHERE Id = @SupervisorId
                AND DeletedOn IS NULL;

            -- Get assigned user's name (using the [Name] column from your table)
            SELECT @AssignedUserName = ISNULL([Name], UserID)
            FROM t_Users WITH (NOLOCK)
            WHERE Id = @CurrentUserId
                AND DeletedOn IS NULL;

            -- Calculate overdue days (convert hours to days)
            SET @OverdueDays = @CurrentHoursOverdue / 24.0;

            -- Send email notification if supervisor has valid email
            IF @SupervisorEmail IS NOT NULL AND LEN(@SupervisorEmail) > 5
            BEGIN
                SET @EmailMessage = CONCAT(
                    'WORKFLOW ESCALATION NOTIFICATION',
                    CHAR(13) + CHAR(10) + CHAR(13) + CHAR(10),
                    'Dear Supervisor,',
                    CHAR(13) + CHAR(10) + CHAR(13) + CHAR(10),
                    'This is to inform you that an action assigned to ', @AssignedUserName, ' has been delayed.',
                    CHAR(13) + CHAR(10) + CHAR(13) + CHAR(10),
                    'DETAILS:',
                    CHAR(13) + CHAR(10),
                    '----------------------------------------',
                    CHAR(13) + CHAR(10),
                    '• Assigned To: ', @AssignedUserName,
                    CHAR(13) + CHAR(10),
                    '• Current Stage: ', @CurrentStageName,
                    CHAR(13) + CHAR(10),
                    '• Delay Duration: ', CAST(ROUND(@OverdueDays, 1) AS NVARCHAR(20)), ' days overdue',
                    CHAR(13) + CHAR(10),
                    '• Current Status: Pending action - Awaiting review/approval',
                    CHAR(13) + CHAR(10) + CHAR(13) + CHAR(10),
                    'ACTION REQUIRED:',
                    CHAR(13) + CHAR(10),
                    '----------------------------------------',
                    CHAR(13) + CHAR(10),
                    'Please assist in:',
                    CHAR(13) + CHAR(10),
                    '1. Following up with ', @AssignedUserName, ' to understand any blockers',
                    CHAR(13) + CHAR(10),
                    '2. Removing any obstacles preventing timely completion',
                    CHAR(13) + CHAR(10),
                    '3. Providing necessary guidance or support',
                    CHAR(13) + CHAR(10) + CHAR(13) + CHAR(10),
                    'This workflow item requires immediate attention to prevent further delays.',
                    CHAR(13) + CHAR(10) + CHAR(13) + CHAR(10),
                    'You can review this item in the ERP System under Workflow Management.',
                    CHAR(13) + CHAR(10) + CHAR(13) + CHAR(10),
                    'Best regards,',
                    CHAR(13) + CHAR(10),
                    'ERP System - Automated Workflow Escalation'
                );

                SET @EmailSubject = CONCAT(
                    'URGENT: Workflow Escalation - ',
                    @CurrentStageName
                );

                -- Send email notification (no Source or SourceID needed)
                EXEC p_sendNotificationEmail
                    @UserID = @SupervisorId,
                    @Subject = @EmailSubject,
                    @Message = @EmailMessage,
                    @SenderId = @ExecutedByUserId,
                    @Source = NULL,
                    @SourceID = NULL;
            END

            FETCH NEXT FROM escalation_cursor INTO
                @CurrentPendingId, @SupervisorId, @CurrentUserId, @CurrentStageName,
                @CurrentHoursOverdue, @IsReEscalation; -- Changed to @IsReEscalation
        END

        CLOSE escalation_cursor;
        DEALLOCATE escalation_cursor;

        -- Return success results
        SELECT
            'SUCCESS' AS Status,
            'Escalation completed successfully' AS Message,
            @ItemsEscalated AS ItemsEscalated,
            @FirstEscalations AS FirstEscalations,
            @ReEscalations AS ReEscalations,
            @StartTime AS StartedAt,
            GETDATE() AS CompletedAt,
            DATEDIFF(SECOND, @StartTime, GETDATE()) AS DurationSeconds;

        -- Optional: Return detailed summary
        SELECT
            ei.StageName,
            COUNT(*) AS ItemsCount,
            SUM(CASE WHEN ei.IsReEscalation = 0 THEN 1 ELSE 0 END) AS FirstEscalations,
            SUM(CASE WHEN ei.IsReEscalation = 1 THEN 1 ELSE 0 END) AS ReEscalations,
            AVG(ei.HoursOverdue) AS AvgHoursOverdue,
            MAX(ei.HoursOverdue) AS MaxHoursOverdue
        FROM #EscalationItems ei
        GROUP BY ei.StageName
        ORDER BY COUNT(*) DESC;

        -- Cleanup
        DROP TABLE #EscalationItems;

    END TRY
    BEGIN CATCH
        -- Rollback transaction if active
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        -- Cleanup cursor if exists
        IF CURSOR_STATUS('local', 'escalation_cursor') >= 0
        BEGIN
            CLOSE escalation_cursor;
            DEALLOCATE escalation_cursor;
        END

        -- Cleanup temp table if exists
        IF OBJECT_ID('tempdb..#EscalationItems') IS NOT NULL
            DROP TABLE #EscalationItems;

        -- Log and return error details
        DECLARE @ErrorMessage NVARCHAR(4000) = ERROR_MESSAGE();

        SELECT
            'ERROR' AS Status,
            'Escalation failed: ' + @ErrorMessage AS Message,
            ERROR_NUMBER() AS ErrorNumber,
            ERROR_LINE() AS ErrorLine,
            ERROR_PROCEDURE() AS ErrorProcedure,
            ERROR_SEVERITY() AS ErrorSeverity,
            ERROR_STATE() AS ErrorState,
            @StartTime AS StartedAt,
            GETDATE() AS FailedAt,
            DATEDIFF(SECOND, @StartTime, GETDATE()) AS DurationBeforeFailure;

        -- Re-throw error for calling application
        THROW;

    END CATCH
END;
