CREATE or ALTER PROCEDURE [dbo].[p_EscalateWorkflows]
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE @StartTime DATETIME = GETDATE();
    DECLARE @ExecutedByUserId BIGINT;
    DECLARE @ItemsEscalated INT = 0;
    DECLARE @FirstEscalations INT = 0;
    DECLARE @ReEscalations INT = 0;

    -- Email variables
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
    DECLARE @IsReEscalation BIT;

    BEGIN TRY
        -- Resolve system user
        SELECT TOP 1 @ExecutedByUserId = Id
        FROM t_Users WITH (NOLOCK)
        WHERE UserID = 'ERPSYS'
          AND DeletedOn IS NULL;

        IF @ExecutedByUserId IS NULL
            SET @ExecutedByUserId = 0;

        BEGIN TRANSACTION;


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

        -- Populate escalation candidates
        INSERT INTO #EscalationItems (
            PendingId,
            StageId,
            UserId,
            StageName,
            EscalationLimit,
            IsReEscalation,
            HoursOverdue,
            SupervisorId
        )
        SELECT
            wp.Id,
            wp.Stage,
            wp.UserId,
            ws.StageName,
            ws.EscalationLimit,
            CASE WHEN wp.EscalatedOn IS NOT NULL THEN 1 ELSE 0 END,
            DATEDIFF(
                HOUR,
                COALESCE(wp.EscalatedOn, wp.CreatedOn),
                GETDATE()
            ) - (ws.EscalationLimit * 24),
            dbo.f_getDepartmentHeadID(wp.UserId)
        FROM dbo.t_WorkFlowPendingTest wp WITH (NOLOCK)
        INNER JOIN dbo.t_WorkFlowStagesTest ws WITH (NOLOCK)
            ON wp.Stage = ws.Id
        WHERE wp.DeletedOn IS NULL
          AND ws.DeletedOn IS NULL
          AND ws.EscalationLimit IS NOT NULL
          AND DATEDIFF(
                HOUR,
                COALESCE(wp.EscalatedOn, wp.CreatedOn),
                GETDATE()
              ) > (ws.EscalationLimit * 24);

        -- Counts
        SELECT
            @ItemsEscalated = COUNT(*),
            @FirstEscalations = SUM(CASE WHEN IsReEscalation = 0 THEN 1 ELSE 0 END),
            @ReEscalations = SUM(CASE WHEN IsReEscalation = 1 THEN 1 ELSE 0 END)
        FROM #EscalationItems;

        -- Nothing to escalate
        IF @ItemsEscalated = 0
        BEGIN
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

            DROP TABLE #EscalationItems;
            RETURN;
        END

        -- Log escalation history
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

        -- Update pending workflow items
        UPDATE wp
        SET
            EscalatedOn = GETDATE(),
            ModifiedBy = @ExecutedByUserId,
            ModifiedOn = GETDATE()
        FROM dbo.t_WorkFlowPendingTest wp
        INNER JOIN #EscalationItems ei
            ON wp.Id = ei.PendingId;

        COMMIT TRANSACTION;

        -- =========================
        -- Cursor declared AFTER temp table exists
        -- =========================
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

        OPEN escalation_cursor;
        FETCH NEXT FROM escalation_cursor INTO
            @CurrentPendingId,
            @SupervisorId,
            @CurrentUserId,
            @CurrentStageName,
            @CurrentHoursOverdue,
            @IsReEscalation;

        WHILE @@FETCH_STATUS = 0
        BEGIN
            SELECT @SupervisorEmail = Email
            FROM t_Users WITH (NOLOCK)
            WHERE Id = @SupervisorId
              AND DeletedOn IS NULL;

            SELECT @AssignedUserName = ISNULL([Name], UserID)
            FROM t_Users WITH (NOLOCK)
            WHERE Id = @CurrentUserId
              AND DeletedOn IS NULL;

            SET @OverdueDays = @CurrentHoursOverdue / 24.0;

            IF @SupervisorEmail IS NOT NULL AND LEN(@SupervisorEmail) > 5
            BEGIN
                SET @EmailSubject =
                    CONCAT('URGENT: Workflow Escalation - ', @CurrentStageName);

                SET @EmailMessage = CONCAT(
                    'WORKFLOW ESCALATION NOTIFICATION', CHAR(13)+CHAR(10)+CHAR(13)+CHAR(10),
                    'Assigned To: ', @AssignedUserName, CHAR(13)+CHAR(10),
                    'Stage: ', @CurrentStageName, CHAR(13)+CHAR(10),
                    'Delay: ', CAST(ROUND(@OverdueDays,1) AS NVARCHAR(20)), ' days overdue'
                );

                EXEC p_sendNotificationEmail
                    @UserID   = @SupervisorId,
                    @Subject  = @EmailSubject,
                    @Message  = @EmailMessage,
                    @SenderId = @ExecutedByUserId,
                    @Source   = NULL,
                    @SourceID = NULL;
            END

            FETCH NEXT FROM escalation_cursor INTO
                @CurrentPendingId,
                @SupervisorId,
                @CurrentUserId,
                @CurrentStageName,
                @CurrentHoursOverdue,
                @IsReEscalation;
        END

        CLOSE escalation_cursor;
        DEALLOCATE escalation_cursor;

        -- Summary output
        SELECT
            'SUCCESS' AS Status,
            'Escalation completed successfully' AS Message,
            @ItemsEscalated AS ItemsEscalated,
            @FirstEscalations AS FirstEscalations,
            @ReEscalations AS ReEscalations,
            @StartTime AS StartedAt,
            GETDATE() AS CompletedAt,
            DATEDIFF(SECOND, @StartTime, GETDATE()) AS DurationSeconds;

        DROP TABLE #EscalationItems;

    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0
            ROLLBACK TRANSACTION;

        IF CURSOR_STATUS('local', 'escalation_cursor') >= 0
        BEGIN
            CLOSE escalation_cursor;
            DEALLOCATE escalation_cursor;
        END

        IF OBJECT_ID('tempdb..#EscalationItems') IS NOT NULL
            DROP TABLE #EscalationItems;

        THROW;
    END CATCH
END;
GO
