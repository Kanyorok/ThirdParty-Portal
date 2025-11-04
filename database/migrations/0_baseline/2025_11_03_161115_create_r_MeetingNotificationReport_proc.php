<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("CREATE   PROCEDURE [dbo].[r_MeetingNotificationReport](
    @FromDate DATETIME = NULL,
    @ToDate DATETIME = NULL,
    @MeetingTitle BIGINT = NULL
)
AS
BEGIN
    SET NOCOUNT ON;

    -- Drop temporary table if it exists
    --IF OBJECT_ID('tempdb..#MeetingNotificationReport') IS NOT NULL
    DROP TABLE if exists #MeetingNotificationReport;

    -- Create temporary table
    CREATE TABLE #MeetingNotificationReport
    (
        MeetingTitle        VARCHAR(1000),
        MeetingType         VARCHAR(1000),
        MeetingStartOn      DATETIME,
        MeetingEndOn        DATETIME,
        MeetingNotes        VARCHAR(MAX),
        MeetingStatus       VARCHAR(200),
        MeetingCreatedBy    VARCHAR(1000),
        MeetingCreatedOn    DATETIME,
        MeetingLocation     VARCHAR(200),
        MeetingLocationType VARCHAR(200),
        UserReminderOn      DATETIME,
        UserDecidedOn       DATETIME,
        UserStatus          VARCHAR(200),
        ScheduleTitle       VARCHAR(MAX),
        ScheduleNotes       VARCHAR(400),
        ScheduleType        VARCHAR(200),
        ScheduleStatus      VARCHAR(200),
        UserName            VARCHAR(200),
        ClientName          VARCHAR(200),
        ClientRemiderON     DATETIME
    );

    -- Insert data
    INSERT INTO #MeetingNotificationReport (MeetingTitle,
                                            MeetingType,
                                            MeetingStartOn,
                                            MeetingEndOn,
                                            MeetingNotes,
                                            MeetingStatus,
                                            MeetingCreatedBy,
                                            MeetingCreatedOn,
                                            MeetingLocation,
                                            MeetingLocationType,
                                            UserName,
                                            UserReminderOn,
                                            UserDecidedOn,
                                            UserStatus,
                                            ScheduleTitle,
                                            ScheduleNotes,
                                            ScheduleType,
                                            ScheduleStatus,
                                            ClientName,
                                            ClientRemiderON)
    SELECT m.Title,
           CASE
               WHEN m.[Type] = 'ClientID' THEN 'Member'
               WHEN m.[Type] = 'LeadID' THEN 'Leads'
               WHEN m.[Type] = 'UserID' THEN 'User'
               END AS MeetingType,
           m.StartOn,
           m.EndOn,
           m.Notes,
           CASE
               WHEN m.StatusID = 'cc' THEN 'Canceled'
               WHEN m.StatusID = 'sc' THEN 'Scheduled'
               WHEN m.StatusID = 'so' THEN 'Ongoing'
               WHEN m.StatusID = 'ss' THEN 'Completed'
               END AS MeetingStatus,
           m.CreatedBy,
           m.CreatedOn,
           m.[Location],
           CASE
               WHEN m.MeetingLocationType = 'lo' THEN 'Local'
               WHEN m.MeetingLocationType = 'on' THEN 'Online'
               WHEN m.MeetingLocationType = 'ph' THEN 'Physical'
               END AS MeetingLocationType,
           u.UserID,
           u.ReminderOn,
           u.DecidedOn,
           u.ScheduleUserStatus,
           s.Title,
           s.Notes,
           s.ScheduledType,
           s.ScheduleStatusID,
           c.ClientID,
           c.ReminderOn
    FROM t_Schedule s
             JOIN t_Meetings m ON s.ScheduledTypeID = m.MeetingID AND m.StatusID = 'sc'
             LEFT JOIN t_ScheduleUsers u ON s.ScheduleID = u.ScheduleId
             LEFT JOIN t_ScheduleClients c ON s.ScheduleID = c.ScheduleId
    WHERE s.ScheduleStatusID = 'sc'
      AND (@FromDate IS NULL OR m.CreatedOn >= @FromDate)
      AND (@ToDate IS NULL OR m.CreatedOn < DATEADD(DAY, 1, @ToDate))
      AND (@MeetingTitle IS NULL OR m.MeetingID = @MeetingTitle);

    -- Update client and user names
    UPDATE r
    SET r.ClientName = c.Name
    FROM #MeetingNotificationReport r
             JOIN dbo.syn_t_Client c ON r.ClientName = c.ClientID;

    UPDATE r
    SET r.UserName = u.Name
    FROM #MeetingNotificationReport r
             JOIN t_Users u ON r.UserName = u.UserID;

    -- Return final result
    SELECT * FROM #MeetingNotificationReport;

    SET NOCOUNT OFF;
END;


--GO
");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS r_MeetingNotificationReport");
    }
};
